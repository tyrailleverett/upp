import type { Incident } from "@/types/models";

const INCIDENT_STATUS_CONFIG: Record<
  string,
  { label: string; bg: string; text: string; border: string }
> = {
  investigating: {
    label: "Investigating",
    bg: "bg-red-50 dark:bg-red-950",
    text: "text-red-800 dark:text-red-200",
    border: "border-red-200 dark:border-red-800",
  },
  identified: {
    label: "Identified",
    bg: "bg-orange-50 dark:bg-orange-950",
    text: "text-orange-800 dark:text-orange-200",
    border: "border-orange-200 dark:border-orange-800",
  },
  monitoring: {
    label: "Monitoring",
    bg: "bg-yellow-50 dark:bg-yellow-950",
    text: "text-yellow-800 dark:text-yellow-200",
    border: "border-yellow-200 dark:border-yellow-800",
  },
  resolved: {
    label: "Resolved",
    bg: "bg-green-50 dark:bg-green-950",
    text: "text-green-800 dark:text-green-200",
    border: "border-green-200 dark:border-green-800",
  },
};

interface IncidentBannerProps {
  incidents: Incident[];
}

export function IncidentBanner({ incidents }: IncidentBannerProps) {
  if (incidents.length === 0) {
    return null;
  }

  return (
    <div className="space-y-3">
      {incidents.map((incident) => {
        const config =
          INCIDENT_STATUS_CONFIG[incident.status] ??
          INCIDENT_STATUS_CONFIG.investigating;
        const latestUpdate = incident.updates?.[0];

        return (
          <a
            className={`block rounded-lg border px-4 py-4 ${config.bg} ${config.border}`}
            href={`#incident-${incident.id}`}
            key={incident.id}
          >
            <div className="flex items-start justify-between gap-4">
              <div className="min-w-0 flex-1">
                <p className={`font-semibold text-sm ${config.text}`}>
                  {incident.title}
                </p>
                {latestUpdate ? (
                  <p
                    className={`mt-1 line-clamp-2 text-sm opacity-80 ${config.text}`}
                  >
                    {latestUpdate.message}
                  </p>
                ) : null}
              </div>
              <span
                className={`shrink-0 rounded-full px-2 py-0.5 font-medium text-xs ${config.bg} ${config.text} border ${config.border}`}
              >
                {config.label}
              </span>
            </div>
          </a>
        );
      })}
    </div>
  );
}
