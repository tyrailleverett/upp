import type { ReactNode } from "react";
import { AppContent } from "@/components/app/app-content";
import { AppHeader } from "@/components/app/app-header";
import { AppShell } from "@/components/app/app-shell";
import { Toaster } from "@/components/ui/sonner";
import { BreadcrumbProvider } from "@/hooks/use-breadcrumbs";
import { useFlashToasts } from "@/hooks/use-flash-toasts";

export default function AppHeaderLayout({ children }: { children: ReactNode }) {
  useFlashToasts();

  return (
    <BreadcrumbProvider>
      <AppShell>
        <AppHeader />
        <AppContent>{children}</AppContent>
      </AppShell>
      <Toaster />
    </BreadcrumbProvider>
  );
}
