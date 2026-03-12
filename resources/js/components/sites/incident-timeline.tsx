import { IncidentStatusBadge } from "@/components/sites/incident-status-badge";
import type { IncidentUpdate } from "@/types/models";

type Props = {
  updates: IncidentUpdate[];
};

export function IncidentTimeline({ updates }: Props) {
  if (updates.length === 0) {
    return <p className="text-muted-foreground text-sm">No updates yet.</p>;
  }

  return (
    <div className="relative space-y-6">
      <div className="absolute top-0 bottom-0 left-2 w-px bg-border" />

      {updates.map((update) => (
        <div className="relative flex gap-4" key={update.id}>
          <div className="relative mt-0.5 size-4 shrink-0 rounded-full border-2 border-background bg-border" />

          <div className="min-w-0 flex-1 space-y-1 pb-2">
            <div className="flex flex-wrap items-center gap-2">
              <IncidentStatusBadge status={update.status} />
              <span className="text-muted-foreground text-xs">
                {new Date(update.created_at).toLocaleString()}
              </span>
            </div>
            <p className="whitespace-pre-wrap text-sm">{update.message}</p>
          </div>
        </div>
      ))}
    </div>
  );
}
