import { cn } from "@/lib/utils";
import type { SiteVisibility } from "@/types/models";

type Props = {
  visibility: SiteVisibility;
  className?: string;
};

const visibilityConfig: Record<
  SiteVisibility,
  { label: string; className: string }
> = {
  draft: {
    label: "Draft",
    className: "bg-secondary text-secondary-foreground border-border",
  },
  published: {
    label: "Published",
    className:
      "bg-green-500/15 text-green-700 border-green-500/30 dark:text-green-400",
  },
  suspended: {
    label: "Suspended",
    className: "bg-destructive/15 text-destructive border-destructive/30",
  },
};

export function VisibilityBadge({ visibility, className }: Props) {
  const config = visibilityConfig[visibility];

  return (
    <span
      className={cn(
        "inline-flex items-center rounded-full border px-2 py-0.5 font-medium text-xs",
        config.className,
        className
      )}
    >
      {config.label}
    </span>
  );
}
