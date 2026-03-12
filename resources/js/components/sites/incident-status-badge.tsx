import { cn } from "@/lib/utils";
import type { IncidentStatus } from "@/types/models";

type Props = {
  status: IncidentStatus;
  className?: string;
};

const statusConfig: Record<
  IncidentStatus,
  { label: string; dotClassName: string; badgeClassName: string }
> = {
  investigating: {
    label: "Investigating",
    dotClassName: "bg-red-500",
    badgeClassName:
      "bg-red-500/15 text-red-700 border-red-500/30 dark:text-red-400",
  },
  identified: {
    label: "Identified",
    dotClassName: "bg-orange-500",
    badgeClassName:
      "bg-orange-500/15 text-orange-700 border-orange-500/30 dark:text-orange-400",
  },
  monitoring: {
    label: "Monitoring",
    dotClassName: "bg-yellow-500",
    badgeClassName:
      "bg-yellow-500/15 text-yellow-700 border-yellow-500/30 dark:text-yellow-400",
  },
  resolved: {
    label: "Resolved",
    dotClassName: "bg-green-500",
    badgeClassName:
      "bg-green-500/15 text-green-700 border-green-500/30 dark:text-green-400",
  },
};

export function IncidentStatusBadge({ status, className }: Props) {
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
