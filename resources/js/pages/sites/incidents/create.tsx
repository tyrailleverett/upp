import { Head, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import {
  create,
  index,
  store,
} from "@/actions/App/Http/Controllers/Sites/IncidentController";
import {
  index as siteIndex,
  show as siteShow,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { ComponentMultiSelect } from "@/components/sites/component-multi-select";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Field,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Component, IncidentStatus, Site } from "@/types/models";

type Props = {
  site: Site;
  components: Component[];
};

const incidentStatusOptions: { value: IncidentStatus; label: string }[] = [
  { value: "investigating", label: "Investigating" },
  { value: "identified", label: "Identified" },
  { value: "monitoring", label: "Monitoring" },
  { value: "resolved", label: "Resolved" },
];

export default function IncidentsCreate({ site, components }: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Incidents", href: index.url(site) },
    { label: "Report Incident", href: create.url(site) },
  ]);

  const { data, setData, post, processing, errors } = useForm<{
    title: string;
    status: IncidentStatus;
    message: string;
    component_ids: number[];
  }>({
    title: "",
    status: "investigating",
    message: "",
    component_ids: [],
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url(site));
  }

  return (
    <>
      <Head title={`Report Incident — ${site.name}`} />

      <div className="space-y-6">
        <h1 className="font-semibold text-2xl tracking-tight">
          Report Incident
        </h1>

        <Card>
          <CardHeader>
            <CardTitle>Incident Details</CardTitle>
            <CardDescription>
              Report a new incident affecting your services.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form className="space-y-6" onSubmit={submit}>
              <FieldSet>
                <FieldGroup>
                  <Field data-invalid={!!errors.title}>
                    <FieldLabel htmlFor="title">Title</FieldLabel>
                    <Input
                      aria-invalid={!!errors.title}
                      id="title"
                      onChange={(e) => setData("title", e.target.value)}
                      placeholder="API degraded performance"
                      required
                      value={data.title}
                    />
                    <FieldError>{errors.title}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.status}>
                    <FieldLabel htmlFor="status">Status</FieldLabel>
                    <Select
                      onValueChange={(v) =>
                        setData("status", v as IncidentStatus)
                      }
                      value={data.status}
                    >
                      <SelectTrigger aria-invalid={!!errors.status} id="status">
                        <SelectValue placeholder="Select status" />
                      </SelectTrigger>
                      <SelectContent>
                        {incidentStatusOptions.map((option) => (
                          <SelectItem key={option.value} value={option.value}>
                            {option.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FieldError>{errors.status}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.message}>
                    <FieldLabel htmlFor="message">Initial Update</FieldLabel>
                    <Textarea
                      aria-invalid={!!errors.message}
                      id="message"
                      onChange={(e) => setData("message", e.target.value)}
                      placeholder="Describe what is happening..."
                      required
                      rows={4}
                      value={data.message}
                    />
                    <FieldError>{errors.message}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.component_ids}>
                    <FieldLabel>Affected Components</FieldLabel>
                    <ComponentMultiSelect
                      components={components}
                      onChange={(ids: number[]) =>
                        setData("component_ids", ids)
                      }
                      selectedIds={data.component_ids}
                    />
                    <FieldError>{errors.component_ids}</FieldError>
                  </Field>
                </FieldGroup>
              </FieldSet>

              <div className="flex items-center gap-3">
                <Button disabled={processing} type="submit">
                  {processing ? "Reporting..." : "Report Incident"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

IncidentsCreate.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
