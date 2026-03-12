import { Head, Link } from "@inertiajs/react";
import { PlusIcon } from "lucide-react";
import { useState } from "react";
import {
  create,
  index,
  show,
} from "@/actions/App/Http/Controllers/Sites/IncidentController";
import {
  index as siteIndex,
  show as siteShow,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { IncidentStatusBadge } from "@/components/sites/incident-status-badge";
import { Button } from "@/components/ui/button";
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from "@/components/ui/empty";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Incident, IncidentUpdate, Site } from "@/types/models";

type Props = {
  site: Site;
  incidents: Incident[];
};

type FilterTab = "all" | "open" | "resolved";

export default function IncidentsIndex({ site, incidents }: Props) {
  const [activeTab, setActiveTab] = useState<FilterTab>("all");

  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Incidents", href: index.url(site) },
  ]);

  const filteredIncidents = incidents.filter((incident) => {
    if (activeTab === "open") {
      return incident.status !== "resolved";
    }
    if (activeTab === "resolved") {
      return incident.status === "resolved";
    }
    return true;
  });

  const openCount = incidents.filter((i) => i.status !== "resolved").length;
  const resolvedCount = incidents.filter((i) => i.status === "resolved").length;

  return (
    <>
      <Head title={`Incidents — ${site.name}`} />

      <div className="space-y-6">
        <div className="flex items-center justify-between gap-4">
          <h1 className="font-semibold text-2xl tracking-tight">
            Incidents
            <span className="ml-2 font-normal text-lg text-muted-foreground">
              {site.name}
            </span>
          </h1>
          <Button asChild>
            <Link href={create.url(site)}>
              <PlusIcon />
              Report Incident
            </Link>
          </Button>
        </div>

        <div className="flex gap-1 border-b">
          {(
            [
              { key: "all", label: "All", count: incidents.length },
              { key: "open", label: "Open", count: openCount },
              { key: "resolved", label: "Resolved", count: resolvedCount },
            ] as { key: FilterTab; label: string; count: number }[]
          ).map((tab) => (
            <button
              className={`border-b-2 px-4 py-2 font-medium text-sm transition-colors ${
                activeTab === tab.key
                  ? "border-primary text-foreground"
                  : "border-transparent text-muted-foreground hover:text-foreground"
              }`}
              key={tab.key}
              onClick={() => setActiveTab(tab.key)}
              type="button"
            >
              {tab.label}
              <span className="ml-1.5 rounded-full bg-muted px-1.5 py-0.5 text-xs">
                {tab.count}
              </span>
            </button>
          ))}
        </div>

        {filteredIncidents.length === 0 ? (
          <Empty>
            <EmptyHeader>
              <EmptyMedia variant="icon">
                <PlusIcon />
              </EmptyMedia>
              <EmptyTitle>No incidents</EmptyTitle>
              <EmptyDescription>
                {activeTab === "all" &&
                  "No incidents have been reported for this site."}
                {activeTab === "open" &&
                  "No open incidents. Everything is running smoothly."}
                {activeTab === "resolved" && "No resolved incidents yet."}
              </EmptyDescription>
            </EmptyHeader>
            {activeTab === "all" && (
              <EmptyContent>
                <Button asChild>
                  <Link href={create.url(site)}>
                    <PlusIcon />
                    Report first incident
                  </Link>
                </Button>
              </EmptyContent>
            )}
          </Empty>
        ) : (
          <div className="divide-y rounded-lg border">
            {filteredIncidents.map((incident) => (
              <IncidentRow incident={incident} key={incident.id} site={site} />
            ))}
          </div>
        )}
      </div>
    </>
  );
}

function IncidentRow({ incident, site }: { incident: Incident; site: Site }) {
  const latestUpdate = getLatestIncidentUpdate(incident);

  const affectedComponents = incident.components ?? [];

  return (
    <Link
      className="flex items-start gap-4 px-4 py-4 transition-colors hover:bg-muted/50"
      href={show.url({ site, incident })}
    >
      <div className="min-w-0 flex-1 space-y-1">
        <div className="flex items-center gap-2">
          <p className="truncate font-medium text-sm">{incident.title}</p>
          <IncidentStatusBadge status={incident.status} />
        </div>

        {affectedComponents.length > 0 && (
          <div className="flex flex-wrap gap-1">
            {affectedComponents.map((component) => (
              <span
                className="rounded bg-muted px-1.5 py-0.5 text-muted-foreground text-xs"
                key={component.id}
              >
                {component.name}
              </span>
            ))}
          </div>
        )}

        {latestUpdate && (
          <>
            <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-muted-foreground text-xs">
              <span>
                Latest update {formatTimestamp(latestUpdate.created_at)}
              </span>
              <span>Started {formatRelativeTime(incident.created_at)}</span>
            </div>
            <p className="line-clamp-1 text-muted-foreground text-xs">
              {latestUpdate.message}
            </p>
          </>
        )}

        {!latestUpdate && (
          <div className="text-muted-foreground text-xs">
            Started {formatRelativeTime(incident.created_at)}
          </div>
        )}
      </div>

      <div className="shrink-0 text-right text-muted-foreground text-xs">
        <div>
          {formatTimestamp(latestUpdate?.created_at ?? incident.created_at)}
        </div>
        <div>{formatDate(incident.created_at)}</div>
      </div>
    </Link>
  );
}

function getLatestIncidentUpdate(incident: Incident) {
  return (incident.updates ?? []).reduce<IncidentUpdate | null>(
    (latest, update) => {
      if (latest === null) {
        return update;
      }

      return new Date(update.created_at) > new Date(latest.created_at)
        ? update
        : latest;
    },
    null
  );
}

function formatDate(value: string) {
  return new Date(value).toLocaleDateString();
}

function formatTimestamp(value: string) {
  return new Date(value).toLocaleString();
}

function formatRelativeTime(value: string) {
  const seconds = Math.round((new Date(value).getTime() - Date.now()) / 1000);
  const formatter = new Intl.RelativeTimeFormat(undefined, { numeric: "auto" });

  const intervals = [
    { unit: "day", seconds: 86_400 },
    { unit: "hour", seconds: 3600 },
    { unit: "minute", seconds: 60 },
  ] as const;

  for (const interval of intervals) {
    if (Math.abs(seconds) >= interval.seconds) {
      return formatter.format(
        Math.round(seconds / interval.seconds),
        interval.unit
      );
    }
  }

  return formatter.format(seconds, "second");
}

IncidentsIndex.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
