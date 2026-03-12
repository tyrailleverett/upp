import { Head, Link, router } from "@inertiajs/react";
import { CheckIcon, PlusIcon } from "lucide-react";
import CompleteMaintenanceController from "@/actions/App/Http/Controllers/Sites/CompleteMaintenanceController";
import {
  create,
  edit,
  index,
} from "@/actions/App/Http/Controllers/Sites/MaintenanceWindowController";
import {
  index as siteIndex,
  show as siteShow,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyTitle,
} from "@/components/ui/empty";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Component, MaintenanceWindow, Site } from "@/types/models";

type Props = {
  site: Site;
  active: MaintenanceWindow[];
  upcoming: MaintenanceWindow[];
  completed: MaintenanceWindow[];
};

function formatDateRange(scheduledAt: string, endsAt: string): string {
  const start = new Date(scheduledAt);
  const end = new Date(endsAt);
  const durationMs = end.getTime() - start.getTime();
  const durationHours = Math.round(durationMs / (1000 * 60 * 60));

  return `${start.toLocaleString()} — ${end.toLocaleString()} (${durationHours}h)`;
}

function MaintenanceCard({
  site,
  window: mw,
  variant,
}: {
  site: Site;
  window: MaintenanceWindow & { components?: Component[] };
  variant: "active" | "upcoming" | "completed";
}) {
  function handleComplete() {
    router.post(
      CompleteMaintenanceController.url({ site, maintenanceWindow: mw })
    );
  }

  return (
    <Card>
      <CardHeader className="flex flex-row items-start justify-between gap-4">
        <div className="space-y-1">
          <CardTitle className="text-base">{mw.title}</CardTitle>
          {mw.description && (
            <p className="text-muted-foreground text-sm">{mw.description}</p>
          )}
        </div>
        <div className="flex items-center gap-2">
          {variant === "active" && <Badge variant="default">Active</Badge>}
          {variant === "upcoming" && (
            <Badge variant="secondary">Scheduled</Badge>
          )}
          {variant === "completed" && (
            <Badge variant="outline">Completed</Badge>
          )}
        </div>
      </CardHeader>
      <CardContent className="space-y-3">
        <p className="text-muted-foreground text-sm">
          {formatDateRange(mw.scheduled_at, mw.ends_at)}
        </p>

        {mw.components && mw.components.length > 0 && (
          <div className="flex flex-wrap gap-1">
            {mw.components.map((component) => (
              <Badge key={component.id} variant="outline">
                {component.name}
              </Badge>
            ))}
          </div>
        )}

        {variant === "active" && (
          <div className="flex gap-2 pt-1">
            <Button onClick={handleComplete} size="sm" variant="secondary">
              <CheckIcon />
              Complete Now
            </Button>
          </div>
        )}

        {variant === "upcoming" && (
          <div className="flex gap-2 pt-1">
            <Button asChild size="sm" variant="secondary">
              <Link href={edit.url({ site, maintenanceWindow: mw })}>Edit</Link>
            </Button>
          </div>
        )}
      </CardContent>
    </Card>
  );
}

export default function MaintenanceIndex({
  site,
  active,
  upcoming,
  completed,
}: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Maintenance Windows", href: index.url(site) },
  ]);

  return (
    <>
      <Head title={`Maintenance Windows — ${site.name}`} />

      <div className="space-y-8">
        <div className="flex items-center justify-between gap-4">
          <h1 className="font-semibold text-2xl tracking-tight">
            Maintenance Windows
            <span className="ml-2 font-normal text-lg text-muted-foreground">
              {site.name}
            </span>
          </h1>
          <Button asChild>
            <Link href={create.url(site)}>
              <PlusIcon />
              Schedule Maintenance
            </Link>
          </Button>
        </div>

        {active.length > 0 && (
          <section className="space-y-3">
            <h2 className="font-semibold text-lg">Active</h2>
            <div className="space-y-3">
              {active.map((mw) => (
                <MaintenanceCard
                  key={mw.id}
                  site={site}
                  variant="active"
                  window={mw}
                />
              ))}
            </div>
          </section>
        )}

        <section className="space-y-3">
          <h2 className="font-semibold text-lg">Upcoming</h2>
          {upcoming.length === 0 ? (
            <Empty>
              <EmptyHeader>
                <EmptyContent>
                  <EmptyTitle>No upcoming maintenance</EmptyTitle>
                  <EmptyDescription>
                    Schedule a maintenance window to notify your users in
                    advance.
                  </EmptyDescription>
                </EmptyContent>
              </EmptyHeader>
            </Empty>
          ) : (
            <div className="space-y-3">
              {upcoming.map((mw) => (
                <MaintenanceCard
                  key={mw.id}
                  site={site}
                  variant="upcoming"
                  window={mw}
                />
              ))}
            </div>
          )}
        </section>

        {completed.length > 0 && (
          <section className="space-y-3">
            <h2 className="font-semibold text-lg">Completed</h2>
            <div className="space-y-3">
              {completed.map((mw) => (
                <MaintenanceCard
                  key={mw.id}
                  site={site}
                  variant="completed"
                  window={mw}
                />
              ))}
            </div>
          </section>
        )}
      </div>
    </>
  );
}

MaintenanceIndex.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
