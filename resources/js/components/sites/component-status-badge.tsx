import { cn } from "@/lib/utils";
import type { ComponentStatus } from "@/types/models";

type Props = {
  status: ComponentStatus;
  className?: string;
};

const statusConfig: Record<
  ComponentStatus,
  { label: string; dotClassName: string; badgeClassName: string }
> = {
  operational: {
    label: "Operational",
    dotClassName: "bg-green-500",
    badgeClassName:
      "bg-green-500/15 text-green-700 border-green-500/30 dark:text-green-400",
  },
  degraded_performance: {
    label: "Degraded Performance",
    dotClassName: "bg-yellow-500",
    badgeClassName:
      "bg-yellow-500/15 text-yellow-700 border-yellow-500/30 dark:text-yellow-400",
  },
  partial_outage: {
    label: "Partial Outage",
    dotClassName: "bg-orange-500",
    badgeClassName:
      "bg-orange-500/15 text-orange-700 border-orange-500/30 dark:text-orange-400",
  },
  major_outage: {
    label: "Major Outage",
    dotClassName: "bg-red-500",
    badgeClassName: "bg-destructive/15 text-destructive border-destructive/30",
  },
  under_maintenance: {
    label: "Under Maintenance",
    dotClassName: "bg-blue-500",
    badgeClassName:
      "bg-blue-500/15 text-blue-700 border-blue-500/30 dark:text-blue-400",
  },
};

export function ComponentStatusBadge({ status, className }: Props) {
  const config = statusConfig[status];

  return (
    <span
      className={cn(
        "inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 font-medium text-xs",
        config.badgeClassName,
        className
      )}
    >
      <span
        className={cn("size-1.5 shrink-0 rounded-full", config.dotClassName)}
      />
      {config.label}
    </span>
  );
}
