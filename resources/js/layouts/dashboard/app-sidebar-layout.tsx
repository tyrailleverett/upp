import type { ReactNode } from "react";
import { AppContent } from "@/components/app/app-content";
import { AppShell } from "@/components/app/app-shell";
import { AppSidebarHeader } from "@/components/app/app-sidebar-header";
import { AppSidebar } from "@/components/dashboard/app-sidebar";
import { Toaster } from "@/components/ui/sonner";
import { BreadcrumbProvider } from "@/hooks/use-breadcrumbs";
import { useFlashToasts } from "@/hooks/use-flash-toasts";

export default function AppSidebarLayout({
  children,
}: {
  children: ReactNode;
}) {
  useFlashToasts();

  return (
    <BreadcrumbProvider>
      <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent className="overflow-x-hidden" variant="sidebar">
          <AppSidebarHeader />
          <div className="flex flex-1 flex-col gap-4 p-4">{children}</div>
        </AppContent>
      </AppShell>
      <Toaster />
    </BreadcrumbProvider>
  );
}
