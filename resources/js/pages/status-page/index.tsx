import { Head } from "@inertiajs/react";
import { ComponentGrid } from "@/components/status-page/component-grid";
import { IncidentBanner } from "@/components/status-page/incident-banner";
import { IncidentHistory } from "@/components/status-page/incident-history";
import { MaintenanceSchedule } from "@/components/status-page/maintenance-schedule";
import { OverallStatusBanner } from "@/components/status-page/overall-status-banner";
import { UptimeChart } from "@/components/status-page/uptime-chart";
import { useStatusPagePolling } from "@/hooks/use-status-page-polling";
import StatusPageLayout from "@/layouts/status-page-layout";
import type {
  ComponentStatus,
  ComponentUptime,
  Incident,
  MaintenanceWindow,
  PublicComponent,
} from "@/types/models";

type SiteProps = {
  name: string;
  slug: string;
  description: string | null;
  accent_color: string | null;
  logo_path: string | null;
  favicon_path: string | null;
  meta_title: string | null;
  meta_description: string | null;
  custom_css: string | null;
};

type Props = {
  site: SiteProps;
  overall_status: ComponentStatus;
  components: PublicComponent[];
  effective_statuses: Record<number, ComponentStatus>;
  open_incidents: Incident[];
  incident_history: Incident[];
  upcoming_maintenance: MaintenanceWindow[];
  active_maintenance: MaintenanceWindow[];
  uptime_history: ComponentUptime[];
};

export default function StatusPageIndex({
  site,
  overall_status: initialOverallStatus,
  components: initialComponents,
  effective_statuses: initialEffectiveStatuses,
  open_incidents: initialOpenIncidents,
  incident_history: initialIncidentHistory,
  upcoming_maintenance: initialUpcomingMaintenance,
  active_maintenance: initialActiveMaintenance,
  uptime_history,
}: Props) {
  const {
    overallStatus,
    components,
    effectiveStatuses,
    openIncidents,
    incidentHistory,
    upcomingMaintenance,
    activeMaintenance,
  } = useStatusPagePolling({
    slug: site.slug,
    initialOverallStatus,
    initialComponents,
    initialEffectiveStatuses,
    initialOpenIncidents,
    initialIncidentHistory,
    initialUpcomingMaintenance,
    initialActiveMaintenance,
  });

  const pageTitle = site.meta_title ?? site.name;
  const pageDescription = site.meta_description ?? site.description;

  const uptimeComponentIds = new Set(
    uptime_history.map((u: ComponentUptime) => u.component_id)
  );
  const uptimeComponents = components.filter((c: PublicComponent) =>
    uptimeComponentIds.has(c.id)
  );

  return (
    <>
      <Head title={pageTitle}>
        {pageDescription && (
          <meta content={pageDescription} name="description" />
        )}
      </Head>

      {site.custom_css && <style>{site.custom_css}</style>}

      <div className="space-y-6">
        <OverallStatusBanner
          accentColor={site.accent_color}
          status={overallStatus}
        />

        {openIncidents.length > 0 && (
          <IncidentBanner incidents={openIncidents} />
        )}

        {activeMaintenance.length > 0 && (
          <section>
            <h2 className="mb-3 font-semibold text-gray-900 text-sm uppercase tracking-wider dark:text-white">
              Active Maintenance
            </h2>
            <MaintenanceSchedule windows={activeMaintenance} />
          </section>
        )}

        <section>
          <h2 className="mb-3 font-semibold text-gray-900 text-sm uppercase tracking-wider dark:text-white">
            Components
          </h2>
          <ComponentGrid
            components={components}
            effectiveStatuses={effectiveStatuses}
          />
        </section>

        {upcomingMaintenance.length > 0 && (
          <section>
            <h2 className="mb-3 font-semibold text-gray-900 text-sm uppercase tracking-wider dark:text-white">
              Scheduled Maintenance
            </h2>
            <MaintenanceSchedule windows={upcomingMaintenance} />
          </section>
        )}

        {uptimeComponents.length > 0 && (
          <section>
            <h2 className="mb-3 font-semibold text-gray-900 text-sm uppercase tracking-wider dark:text-white">
              Uptime History
            </h2>
            <div className="space-y-4">
              {uptimeComponents.map((component: PublicComponent) => {
                const history = uptime_history.find(
                  (u: ComponentUptime) => u.component_id === component.id
                );
                if (!history) {
                  return null;
                }
                return (
                  <div
                    className="rounded-lg border border-gray-200 bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-800"
                    key={component.id}
                  >
                    <p className="mb-2 font-medium text-gray-700 text-sm dark:text-gray-300">
                      {component.name}
                    </p>
                    <UptimeChart uptimeHistory={history} />
                  </div>
                );
              })}
            </div>
          </section>
        )}

        <section id="incident-history">
          <h2 className="mb-3 font-semibold text-gray-900 text-sm uppercase tracking-wider dark:text-white">
            Incident History
          </h2>
          <IncidentHistory incidents={incidentHistory} />
        </section>
      </div>
    </>
  );
}

StatusPageIndex.layout = (page: React.ReactNode) => {
  const props = (page as React.ReactElement<{ site: SiteProps }>).props.site;
  return (
    <StatusPageLayout
      accentColor={props.accent_color}
      logoPath={props.logo_path}
      siteName={props.name}
    >
      {page}
    </StatusPageLayout>
  );
};
