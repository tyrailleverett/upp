import { Head, router, useForm } from "@inertiajs/react";
import { type FormEvent, useState } from "react";
import {
  destroy,
  edit,
  index,
  show,
  update,
} from "@/actions/App/Http/Controllers/Sites/IncidentController";
import {
  index as siteIndex,
  show as siteShow,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { ComponentMultiSelect } from "@/components/sites/component-multi-select";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
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
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Component, Incident, Site } from "@/types/models";

type Props = {
  site: Site;
  incident: Incident;
  components: Component[];
};

export default function IncidentsEdit({ site, incident, components }: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Incidents", href: index.url(site) },
    { label: incident.title, href: show.url({ site, incident }) },
    { label: "Edit", href: edit.url({ site, incident }) },
  ]);

  const initialComponentIds = incident.components?.map((c) => c.id) ?? [];

  const { data, setData, put, processing, errors } = useForm<{
    title: string;
    component_ids: number[];
  }>({
    title: incident.title,
    component_ids: initialComponentIds,
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    put(update.url({ site, incident }));
  }

  return (
    <>
      <Head title={`Edit Incident — ${site.name}`} />

      <div className="space-y-6">
        <h1 className="font-semibold text-2xl tracking-tight">Edit Incident</h1>

        <Card>
          <CardHeader>
            <CardTitle>Incident Details</CardTitle>
            <CardDescription>
              Update the incident title and affected components. To change
              status, post a timeline update on the incident page.
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
                      required
                      value={data.title}
                    />
                    <FieldError>{errors.title}</FieldError>
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
                  {processing ? "Saving..." : "Save changes"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <DeleteIncidentCard incident={incident} site={site} />
      </div>
    </>
  );
}

function DeleteIncidentCard({
  site,
  incident,
}: {
  site: Site;
  incident: Incident;
}) {
  const [open, setOpen] = useState(false);

  function handleDelete() {
    router.delete(destroy.url({ site, incident }), {
      onSuccess: () => setOpen(false),
    });
  }

  return (
    <Card className="border-destructive/50">
      <CardHeader>
        <CardTitle>Delete Incident</CardTitle>
        <CardDescription>
          Permanently delete this incident and all its timeline updates. This
          action cannot be undone.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <AlertDialog onOpenChange={setOpen} open={open}>
          <AlertDialogTrigger asChild>
            <Button variant="destructive">Delete incident</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete this incident?</AlertDialogTitle>
              <AlertDialogDescription>
                This will permanently delete the incident and all its data. This
                action cannot be undone.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction
                className="bg-destructive text-white hover:bg-destructive/90"
                onClick={handleDelete}
              >
                Delete incident
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </CardContent>
    </Card>
  );
}

IncidentsEdit.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
