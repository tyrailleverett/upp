import type { ComponentStatus, PublicComponent } from "@/types/models";

const STATUS_DOT: Record<ComponentStatus, string> = {
  operational: "bg-green-500",
  degraded_performance: "bg-yellow-500",
  partial_outage: "bg-orange-500",
  major_outage: "bg-red-500",
  under_maintenance: "bg-blue-500",
};

const STATUS_LABEL: Record<ComponentStatus, string> = {
  operational: "Operational",
  degraded_performance: "Degraded",
  partial_outage: "Partial Outage",
  major_outage: "Major Outage",
  under_maintenance: "Maintenance",
};

const STATUS_TEXT: Record<ComponentStatus, string> = {
  operational: "text-green-700 dark:text-green-400",
  degraded_performance: "text-yellow-700 dark:text-yellow-400",
  partial_outage: "text-orange-700 dark:text-orange-400",
  major_outage: "text-red-700 dark:text-red-400",
  under_maintenance: "text-blue-700 dark:text-blue-400",
};

interface ComponentGridProps {
  components: PublicComponent[];
  effectiveStatuses: Record<number, ComponentStatus>;
}

export function ComponentGrid({
  components,
  effectiveStatuses,
}: ComponentGridProps) {
  const groups: Record<string, PublicComponent[]> = {};
  const ungrouped: PublicComponent[] = [];

  for (const component of components) {
    if (component.group) {
      groups[component.group] ??= [];
      groups[component.group].push(component);
    } else {
      ungrouped.push(component);
    }
  }

  const renderComponent = (component: PublicComponent) => {
    const status = effectiveStatuses[component.id] ?? component.status;

    return (
      <div
        className="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
        key={component.id}
      >
        <div>
          <p className="font-medium text-gray-900 text-sm dark:text-white">
            {component.name}
          </p>
          {component.description ? (
            <p className="mt-0.5 text-gray-500 text-xs dark:text-gray-400">
              {component.description}
            </p>
          ) : null}
        </div>
        <div className="flex shrink-0 items-center gap-2">
          <span
            className={`inline-block h-2 w-2 rounded-full ${STATUS_DOT[status]}`}
          />
          <span className={`font-medium text-xs ${STATUS_TEXT[status]}`}>
            {STATUS_LABEL[status]}
          </span>
        </div>
      </div>
    );
  };

  return (
    <div className="space-y-6">
      {ungrouped.length > 0 && (
        <div className="space-y-2">{ungrouped.map(renderComponent)}</div>
      )}
      {Object.entries(groups).map(([group, groupComponents]) => (
        <div key={group}>
          <h3 className="mb-2 font-medium text-gray-500 text-xs uppercase tracking-wider dark:text-gray-400">
            {group}
          </h3>
          <div className="space-y-2">
            {groupComponents.map(renderComponent)}
          </div>
        </div>
      ))}
    </div>
  );
}
