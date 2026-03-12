import { ComponentStatusBadge } from "@/components/sites/component-status-badge";
import { Checkbox } from "@/components/ui/checkbox";
import type { Component } from "@/types/models";

type Props = {
  components: Component[];
  selectedIds: number[];
  onChange: (ids: number[]) => void;
};

export function ComponentMultiSelect({
  components,
  selectedIds,
  onChange,
}: Props) {
  function handleToggle(id: number, checked: boolean) {
    if (checked) {
      onChange([...selectedIds, id]);
    } else {
      onChange(selectedIds.filter((selectedId) => selectedId !== id));
    }
  }

  if (components.length === 0) {
    return (
      <p className="text-muted-foreground text-sm">
        No components available for this site.
      </p>
    );
  }

  return (
    <div className="space-y-2 rounded-md border p-3">
      {components.map((component) => (
        <div
          className="flex cursor-pointer items-center gap-3 rounded p-1 hover:bg-muted/50"
          key={component.id}
        >
          <Checkbox
            checked={selectedIds.includes(component.id)}
            id={`component-${component.id}`}
            onCheckedChange={(checked) =>
              handleToggle(component.id, checked === true)
            }
          />
          <label
            className="flex flex-1 cursor-pointer items-center justify-between gap-2"
            htmlFor={`component-${component.id}`}
          >
            <span className="text-sm">{component.name}</span>
            <ComponentStatusBadge status={component.status} />
          </label>
        </div>
      ))}
    </div>
  );
}
