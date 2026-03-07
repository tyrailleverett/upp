import { createInertiaApp } from "@inertiajs/react";
import createServer from "@inertiajs/react/server";
import { renderToString } from "react-dom/server";
import ToastLayout from "@/layouts/toast-layout";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

const pages = import.meta.glob("./pages/**/*.tsx", { eager: true }) as Record<
  string,
  { default: { layout?: (page: React.ReactNode) => React.ReactNode } }
>;

createServer((page) =>
  createInertiaApp({
    page,
    render: renderToString,
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
      const resolved = pages[`./pages/${name}.tsx`];

      if (!resolved) {
        throw new Error(`Page not found: ${name}`);
      }

      resolved.default.layout ??= (page) => <ToastLayout>{page}</ToastLayout>;

      return resolved.default;
    },
    setup: ({ App, props }) => <App {...props} />,
  })
);
