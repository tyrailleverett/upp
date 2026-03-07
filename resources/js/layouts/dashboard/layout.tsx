import type { ComponentProps } from "react";
import AppSidebarLayout from "@/layouts/dashboard/app-sidebar-layout";

export default function DashboardLayout(
  props: ComponentProps<typeof AppSidebarLayout>
) {
  return <AppSidebarLayout {...props} />;
}
