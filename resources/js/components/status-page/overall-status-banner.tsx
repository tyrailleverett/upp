import type { ComponentStatus } from "@/types/models";

const STATUS_CONFIG: Record<
  ComponentStatus,
  { label: string; bg: string; text: string; border: string; dot: string }
> = {
  operational: {
    label: "All Systems Operational",
    bg: "bg-green-50 dark:bg-green-950",
    text: "text-green-800 dark:text-green-200",
    border: "border-green-200 dark:border-green-800",
    dot: "bg-green-500",
  },
  degraded_performance: {
    label: "Degraded Performance",
    bg: "bg-yellow-50 dark:bg-yellow-950",
    text: "text-yellow-800 dark:text-yellow-200",
    border: "border-yellow-200 dark:border-yellow-800",
    dot: "bg-yellow-500",
  },
  partial_outage: {
    label: "Partial Outage",
    bg: "bg-orange-50 dark:bg-orange-950",
    text: "text-orange-800 dark:text-orange-200",
    border: "border-orange-200 dark:border-orange-800",
    dot: "bg-orange-500",
  },
  major_outage: {
    label: "Major Outage",
    bg: "bg-red-50 dark:bg-red-950",
    text: "text-red-800 dark:text-red-200",
    border: "border-red-200 dark:border-red-800",
    dot: "bg-red-500",
  },
  under_maintenance: {
    label: "Under Maintenance",
    bg: "bg-blue-50 dark:bg-blue-950",
    text: "text-blue-800 dark:text-blue-200",
    border: "border-blue-200 dark:border-blue-800",
    dot: "bg-blue-500",
  },
};

interface OverallStatusBannerProps {
  status: ComponentStatus;
  accentColor: string | null;
}

export function OverallStatusBanner({ status }: OverallStatusBannerProps) {
  const config = STATUS_CONFIG[status];

  return (
    <div
      className={`rounded-xl border px-6 py-6 ${config.bg} ${config.border}`}
    >
      <div className="flex items-center gap-3">
        <span className={`inline-block h-3 w-3 rounded-full ${config.dot}`} />
        <h2 className={`font-semibold text-xl ${config.text}`}>
          {config.label}
        </h2>
      </div>
    </div>
  );
}
