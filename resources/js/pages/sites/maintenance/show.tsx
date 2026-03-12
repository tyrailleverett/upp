import { Head, Link, router } from "@inertiajs/react";
import { CheckIcon, PencilIcon, Trash2Icon } from "lucide-react";
import { useState } from "react";
import CompleteMaintenanceController from "@/actions/App/Http/Controllers/Sites/CompleteMaintenanceController";
import {
  destroy,
  edit,
  index,
  show,
} from "@/actions/App/Http/Controllers/Sites/MaintenanceWindowController";
import {
  index as siteIndex,
  show as siteShow,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Component, MaintenanceWindow, Site } from "@/types/models";

type Props = {
  site: Site;
  maintenanceWindow: MaintenanceWindow & { components: Component[] };
};

export default function MaintenanceShow({
  site,
  maintenanceWindow: mw,
}: Props) {
  const [deleting, setDeleting] = useState(false);

  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Maintenance Windows", href: index.url(site) },
    { label: mw.title, href: show.url({ site, maintenanceWindow: mw }) },
  ]);

  const isActive =
    !mw.completed_at &&
    new Date(mw.scheduled_at) <= new Date() &&
    new Date(mw.ends_at) > new Date();
  const isUpcoming = !mw.completed_at && new Date(mw.scheduled_at) > new Date();

  function handleComplete() {
    router.post(
      CompleteMaintenanceController.url({ site, maintenanceWindow: mw })
    );
  }

  function handleDelete() {
    setDeleting(true);
    router.delete(destroy.url({ site, maintenanceWindow: mw }));
  }

  return (
    <>
      <Head title={`${mw.title} — ${site.name}`} />

      <div className="space-y-6">
        <div className="flex items-start justify-between gap-4">
          <div>
            <h1 className="font-semibold text-2xl tracking-tight">
              {mw.title}
            </h1>
            {mw.description && (
              <p className="mt-1 text-muted-foreground">{mw.description}</p>
            )}
          </div>
          <div className="flex items-center gap-2">
            {mw.completed_at && <Badge variant="outline">Completed</Badge>}
            {isActive && <Badge variant="default">Active</Badge>}
            {isUpcoming && <Badge variant="secondary">Scheduled</Badge>}
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <CardDescription>Time Range</CardDescription>
              <p className="mt-1 text-sm">
                {new Date(mw.scheduled_at).toLocaleString()} —{" "}
                {new Date(mw.ends_at).toLocaleString()}
              </p>
            </div>

            {mw.completed_at && (
              <div>
                <CardDescription>Completed At</CardDescription>
                <p className="mt-1 text-sm">
                  {new Date(mw.completed_at).toLocaleString()}
                </p>
              </div>
            )}

            {mw.components.length > 0 && (
              <div>
                <CardDescription>Affected Components</CardDescription>
                <div className="mt-2 flex flex-wrap gap-1">
                  {mw.components.map((component) => (
                    <Badge key={component.id} variant="outline">
                      {component.name}
                    </Badge>
                  ))}
                </div>
              </div>
            )}
          </CardContent>
        </Card>

        <div className="flex items-center gap-3">
          {isActive && (
            <Button onClick={handleComplete} variant="secondary">
              <CheckIcon />
              Complete Now
            </Button>
          )}

          {isUpcoming && (
            <>
              <Button asChild variant="secondary">
                <Link href={edit.url({ site, maintenanceWindow: mw })}>
                  <PencilIcon />
                  Edit
                </Link>
              </Button>

              <AlertDialog>
                <AlertDialogTrigger asChild>
                  <Button variant="destructive">
                    <Trash2Icon />
                    Delete
                  </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>
                      Delete Maintenance Window
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to delete this maintenance window?
                      This action cannot be undone.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                      disabled={deleting}
                      onClick={handleDelete}
                    >
                      Delete
                    </AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </>
          )}
        </div>
      </div>
    </>
  );
}

MaintenanceShow.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
