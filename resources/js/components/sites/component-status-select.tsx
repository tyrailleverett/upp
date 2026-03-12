import { router } from "@inertiajs/react";
import ComponentStatusController from "@/actions/App/Http/Controllers/Sites/ComponentStatusController";
import { ComponentStatusBadge } from "@/components/sites/component-status-badge";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import type { Component, ComponentStatus } from "@/types/models";

type Props = {
  component: Component;
  siteSlug: string;
};

const statusOptions: { value: ComponentStatus; label: string }[] = [
  { value: "operational", label: "Operational" },
  { value: "degraded_performance", label: "Degraded Performance" },
  { value: "partial_outage", label: "Partial Outage" },
  { value: "major_outage", label: "Major Outage" },
];

export function ComponentStatusSelect({ component, siteSlug }: Props) {
  function handleChange(status: string) {
    router.put(
      ComponentStatusController.url({
        site: siteSlug,
        component: component.id,
      }),
      { status },
      { preserveScroll: true }
    );
  }

  return (
    <Select onValueChange={handleChange} value={component.status}>
      <SelectTrigger className="w-auto" size="sm">
        <SelectValue>
          <ComponentStatusBadge status={component.status} />
        </SelectValue>
      </SelectTrigger>
      <SelectContent>
        {statusOptions.map((option) => (
          <SelectItem key={option.value} value={option.value}>
            <ComponentStatusBadge status={option.value} />
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
}
