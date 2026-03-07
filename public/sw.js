const CACHE_NAME = "laravel-pwa-v1";
const PRE_CACHE_URLS = ["/", "/manifest.json", "/favicon.ico", "/favicon.svg"];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRE_CACHE_URLS))
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys
            .filter((key) => key !== CACHE_NAME)
            .map((key) => caches.delete(key))
        )
      )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const { request } = event;

  if (request.mode === "navigate") {
    event.respondWith(
      fetch(request).catch(() =>
        caches.match("/").then((r) => r ?? new Response("", { status: 504 }))
      )
    );
    return;
  }

  event.respondWith(
    fetch(request).catch(() =>
      caches.match(request).then((r) => r ?? new Response("", { status: 504 }))
    )
  );
});
