import { Head, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import {
  create,
  index,
  store,
} from "@/actions/App/Http/Controllers/Sites/MaintenanceWindowController";
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
import { Textarea } from "@/components/ui/textarea";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import { localDatetimeToUtcIso } from "@/lib/datetime";
import type { Component, Site } from "@/types/models";

type Props = {
  site: Site;
  components: Component[];
};

export default function MaintenanceCreate({ site, components }: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Maintenance Windows", href: index.url(site) },
    { label: "Schedule Maintenance", href: create.url(site) },
  ]);

  const { data, setData, transform, post, processing, errors } = useForm<{
    title: string;
    description: string;
    scheduled_at: string;
    ends_at: string;
    component_ids: number[];
  }>({
    title: "",
    description: "",
    scheduled_at: "",
    ends_at: "",
    component_ids: [],
  });

  function submit(e: FormEvent) {
    e.preventDefault();

    transform((currentData) => ({
      ...currentData,
      scheduled_at: localDatetimeToUtcIso(currentData.scheduled_at),
      ends_at: localDatetimeToUtcIso(currentData.ends_at),
    }));

    post(store.url(site));
  }

  return (
    <>
      <Head title={`Schedule Maintenance — ${site.name}`} />

      <div className="space-y-6">
        <h1 className="font-semibold text-2xl tracking-tight">
          Schedule Maintenance
        </h1>

        <Card>
          <CardHeader>
            <CardTitle>Maintenance Details</CardTitle>
            <CardDescription>
              Schedule a maintenance window to notify users of planned downtime.
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
                      placeholder="Database maintenance"
                      required
                      value={data.title}
                    />
                    <FieldError>{errors.title}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.description}>
                    <FieldLabel htmlFor="description">
                      Description (optional)
                    </FieldLabel>
                    <Textarea
                      aria-invalid={!!errors.description}
                      id="description"
                      onChange={(e) => setData("description", e.target.value)}
                      placeholder="Describe the maintenance being performed..."
                      rows={3}
                      value={data.description}
                    />
                    <FieldError>{errors.description}</FieldError>
                  </Field>

                  <div className="grid gap-4 sm:grid-cols-2">
                    <Field data-invalid={!!errors.scheduled_at}>
                      <FieldLabel htmlFor="scheduled_at">Start Time</FieldLabel>
                      <Input
                        aria-invalid={!!errors.scheduled_at}
                        id="scheduled_at"
                        onChange={(e) =>
                          setData("scheduled_at", e.target.value)
                        }
                        required
                        type="datetime-local"
                        value={data.scheduled_at}
                      />
                      <FieldError>{errors.scheduled_at}</FieldError>
                    </Field>

                    <Field data-invalid={!!errors.ends_at}>
                      <FieldLabel htmlFor="ends_at">End Time</FieldLabel>
                      <Input
                        aria-invalid={!!errors.ends_at}
                        id="ends_at"
                        onChange={(e) => setData("ends_at", e.target.value)}
                        required
                        type="datetime-local"
                        value={data.ends_at}
                      />
                      <FieldError>{errors.ends_at}</FieldError>
                    </Field>
                  </div>

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
                  {processing ? "Scheduling..." : "Schedule Maintenance"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

MaintenanceCreate.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
