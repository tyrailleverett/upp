import type { ReactNode } from "react";

interface StatusPageLayoutProps {
  children: ReactNode;
  siteName: string;
  logoPath?: string | null;
  faviconPath?: string | null;
  accentColor?: string | null;
}

export default function StatusPageLayout({
  children,
  siteName,
  logoPath,
  accentColor,
}: StatusPageLayoutProps) {
  return (
    <div
      className="min-h-screen bg-gray-50 dark:bg-gray-950"
      style={
        accentColor
          ? ({ "--accent": accentColor } as React.CSSProperties)
          : undefined
      }
    >
      <header className="border-gray-200 border-b bg-white dark:border-gray-800 dark:bg-gray-900">
        <div className="mx-auto flex max-w-4xl items-center gap-3 px-4 py-4">
          {logoPath ? (
            <img
              alt={siteName}
              className="h-8 w-auto"
              height={32}
              src={logoPath}
              width={32}
            />
          ) : null}
          <span className="font-semibold text-gray-900 text-lg dark:text-white">
            {siteName}
          </span>
        </div>
      </header>

      <main className="mx-auto max-w-4xl px-4 py-8">{children}</main>

      <footer className="border-gray-200 border-t py-6 dark:border-gray-800">
        <div className="mx-auto flex max-w-4xl items-center justify-center px-4">
          <span className="text-gray-400 text-sm dark:text-gray-600">
            Powered by{" "}
            <span className="font-medium text-gray-500 dark:text-gray-500">
              StatusKit
            </span>
          </span>
        </div>
      </footer>
    </div>
  );
}
