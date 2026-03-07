import type { ReactNode } from "react";
import { SidebarProvider } from "@/components/ui/sidebar";

const SIDEBAR_COOKIE_PATTERN = /(?:^|;\s*)sidebar_state=(\w+)/;

function getSidebarCookie(): boolean {
  if (typeof document === "undefined") {
    return true;
  }

  const match = document.cookie.match(SIDEBAR_COOKIE_PATTERN);
  return match ? match[1] === "true" : true;
}

interface AppShellProps {
  children: ReactNode;
  variant?: "header" | "sidebar";
}

export function AppShell({ children, variant = "header" }: AppShellProps) {
  if (variant === "header") {
    return <div className="flex min-h-screen w-full flex-col">{children}</div>;
  }

  return (
    <SidebarProvider defaultOpen={getSidebarCookie()}>
      {children}
    </SidebarProvider>
  );
}
