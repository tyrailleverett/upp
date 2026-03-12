import { Head } from "@inertiajs/react";

export default function StatusPageSuspended() {
  return (
    <>
      <Head title="Status Page Unavailable" />
      <div className="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-950">
        <div className="mx-auto max-w-md px-6 py-12 text-center">
          <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800">
            <svg
              aria-hidden="true"
              className="h-8 w-8 text-gray-400 dark:text-gray-500"
              fill="none"
              height={32}
              stroke="currentColor"
              strokeWidth={1.5}
              viewBox="0 0 24 24"
              width={32}
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </div>
          <h1 className="font-semibold text-gray-900 text-xl dark:text-white">
            Status Page Unavailable
          </h1>
          <p className="mt-3 text-gray-500 text-sm dark:text-gray-400">
            This status page is currently unavailable. Please check back later.
          </p>
        </div>
      </div>
    </>
  );
}
