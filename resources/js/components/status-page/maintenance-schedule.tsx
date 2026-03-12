import type { MaintenanceWindow } from "@/types/models";

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

interface MaintenanceScheduleProps {
  windows: MaintenanceWindow[];
}

export function MaintenanceSchedule({ windows }: MaintenanceScheduleProps) {
  if (windows.length === 0) {
    return (
      <div className="rounded-lg border border-gray-200 bg-white px-6 py-8 text-center dark:border-gray-700 dark:bg-gray-800">
        <p className="text-gray-500 text-sm dark:text-gray-400">
          No scheduled maintenance
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-3">
      {windows.map((window) => {
        const now = new Date();
        const scheduledAt = new Date(window.scheduled_at);
        const endsAt = new Date(window.ends_at);
        const isActive =
          window.completed_at === null && scheduledAt <= now && endsAt > now;

        return (
          <div
            className="rounded-lg border border-blue-200 bg-blue-50 px-5 py-4 dark:border-blue-800 dark:bg-blue-950/30"
            key={window.id}
          >
            <div className="flex items-start justify-between gap-4">
              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                  <h3 className="font-semibold text-blue-900 text-sm dark:text-blue-100">
                    {window.title}
                  </h3>
                  {isActive && (
                    <span className="rounded-full bg-blue-600 px-2 py-0.5 font-medium text-white text-xs">
                      Active
                    </span>
                  )}
                </div>
                {window.description && (
                  <p className="mt-1 text-blue-700 text-xs dark:text-blue-300">
                    {window.description}
                  </p>
                )}
                {window.components && window.components.length > 0 && (
                  <p className="mt-1 text-blue-600 text-xs dark:text-blue-400">
                    Affected: {window.components.map((c) => c.name).join(", ")}
                  </p>
                )}
              </div>
              <div className="shrink-0 text-right">
                <p className="text-blue-700 text-xs dark:text-blue-300">
                  {formatDate(window.scheduled_at)}
                </p>
                <p className="text-blue-500 text-xs dark:text-blue-500">
                  to {formatDate(window.ends_at)}
                </p>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}
