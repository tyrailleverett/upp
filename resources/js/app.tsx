import { createInertiaApp } from "@inertiajs/react";
import { ThemeProvider } from "next-themes";
import type { ReactNode } from "react";
import { createRoot } from "react-dom/client";
import ToastLayout from "@/layouts/toast-layout";
import "../css/app.css";

function ThemeController({ children }: { children: ReactNode }) {
  return (
    <ThemeProvider attribute="class" defaultTheme="system" enableSystem>
      {children}
    </ThemeProvider>
  );
}

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

const pages = import.meta.glob<{
  default: { layout?: (page: React.ReactNode) => React.ReactNode };
}>("./pages/**/*.tsx");

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  resolve: (name) => {
    const importPage = pages[`./pages/${name}.tsx`];

    if (!importPage) {
      throw new Error(`Page not found: ${name}`);
    }

    return importPage().then((module) => {
      module.default.layout ??= (page) => <ToastLayout>{page}</ToastLayout>;
      return module.default;
    });
  },
  setup({ el, App, props }) {
    const root = createRoot(el);

    root.render(
      <ThemeController>
        <App {...props} />
      </ThemeController>
    );
  },
  progress: {
    color: "#4B5563",
  },
});

if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/sw.js")
      .then((registration) => {
        // Service worker registered successfully; you can use `registration` if needed.
        console.info(
          "Service worker registered with scope:",
          registration.scope
        );
      })
      .catch((error) => {
        console.error("Service worker registration failed:", error);
      });
  });
}
