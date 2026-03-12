import type { Incident, IncidentStatus } from "@/types/models";

const STATUS_CONFIG: Record<
  IncidentStatus,
  { label: string; dot: string; text: string }
> = {
  investigating: {
    label: "Investigating",
    dot: "bg-red-500",
    text: "text-red-600 dark:text-red-400",
  },
  identified: {
    label: "Identified",
    dot: "bg-orange-500",
    text: "text-orange-600 dark:text-orange-400",
  },
  monitoring: {
    label: "Monitoring",
    dot: "bg-yellow-500",
    text: "text-yellow-600 dark:text-yellow-400",
  },
  resolved: {
    label: "Resolved",
    dot: "bg-green-500",
    text: "text-green-600 dark:text-green-400",
  },
};

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

interface IncidentHistoryProps {
  incidents: Incident[];
}

export function IncidentHistory({ incidents }: IncidentHistoryProps) {
  if (incidents.length === 0) {
    return (
      <div className="rounded-lg border border-gray-200 bg-white px-6 py-8 text-center dark:border-gray-700 dark:bg-gray-800">
        <p className="text-gray-500 text-sm dark:text-gray-400">
          No incidents reported
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {incidents.map((incident) => {
        const config =
          STATUS_CONFIG[incident.status] ?? STATUS_CONFIG.investigating;
        const updates = incident.updates ?? [];

        return (
          <div
            className="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
            id={`incident-${incident.id}`}
            key={incident.id}
          >
            <div className="border-gray-200 border-b px-5 py-4 dark:border-gray-700">
              <div className="flex items-start justify-between gap-4">
                <div className="min-w-0 flex-1">
                  <h3 className="font-semibold text-gray-900 text-sm dark:text-white">
                    {incident.title}
                  </h3>
                  {incident.components && incident.components.length > 0 && (
                    <p className="mt-1 text-gray-500 text-xs dark:text-gray-400">
                      Affected:{" "}
                      {incident.components.map((c) => c.name).join(", ")}
                    </p>
                  )}
                </div>
                <div className="flex shrink-0 items-center gap-1.5">
                  <span
                    className={`inline-block h-2 w-2 rounded-full ${config.dot}`}
                  />
                  <span className={`font-medium text-xs ${config.text}`}>
                    {config.label}
                  </span>
                </div>
              </div>
              {incident.resolved_at && (
                <p className="mt-1 text-gray-400 text-xs dark:text-gray-500">
                  Resolved {formatDate(incident.resolved_at)}
                </p>
              )}
            </div>

            {updates.length > 0 && (
              <div className="divide-y divide-gray-100 dark:divide-gray-700/50">
                {updates.map((update) => {
                  const updateConfig =
                    STATUS_CONFIG[update.status] ?? STATUS_CONFIG.investigating;
                  return (
                    <div className="px-5 py-3" key={update.id}>
                      <div className="flex items-center justify-between gap-2">
                        <span
                          className={`font-medium text-xs ${updateConfig.text}`}
                        >
                          {updateConfig.label}
                        </span>
                        <span className="text-gray-400 text-xs dark:text-gray-500">
                          {formatDate(update.created_at)}
                        </span>
                      </div>
                      <p className="mt-1 text-gray-600 text-sm dark:text-gray-300">
                        {update.message}
                      </p>
                    </div>
                  );
                })}
              </div>
            )}

            {incident.postmortem && (
              <div className="border-gray-200 border-t bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                <p className="font-medium text-gray-500 text-xs uppercase tracking-wider dark:text-gray-400">
                  Postmortem
                </p>
                <p className="mt-1 text-gray-600 text-sm dark:text-gray-300">
                  {incident.postmortem}
                </p>
              </div>
            )}
          </div>
        );
      })}
    </div>
  );
}
