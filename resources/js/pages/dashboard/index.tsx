import { Head } from "@inertiajs/react";
import DashboardController from "@/actions/App/Http/Controllers/DashboardController";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";

export default function Dashboard() {
  useSetBreadcrumbs([{ label: "Dashboard", href: DashboardController.url() }]);

  return (
    <>
      <Head title="Dashboard" />
      <h1 className="font-semibold text-2xl tracking-tight">Dashboard</h1>
    </>
  );
}

Dashboard.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
