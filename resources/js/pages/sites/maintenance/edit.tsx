import { Head, router, useForm } from "@inertiajs/react";
import { Trash2Icon } from "lucide-react";
import { type FormEvent, useState } from "react";
import {
  destroy,
  edit,
  index,
  update,
} from "@/actions/App/Http/Controllers/Sites/MaintenanceWindowController";
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
import { Textarea } from "@/components/ui/textarea";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import {
  isoDatetimeToLocalInputValue,
  localDatetimeToUtcIso,
} from "@/lib/datetime";
import type { Component, MaintenanceWindow, Site } from "@/types/models";

type Props = {
  site: Site;
  maintenanceWindow: MaintenanceWindow & { components?: Component[] };
  components: Component[];
};

export default function MaintenanceEdit({
  site,
  maintenanceWindow: mw,
  components,
}: Props) {
  const [deleting, setDeleting] = useState(false);

  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Maintenance Windows", href: index.url(site) },
    { label: mw.title, href: edit.url({ site, maintenanceWindow: mw }) },
  ]);

  const { data, setData, transform, put, processing, errors } = useForm<{
    title: string;
    description: string;
    scheduled_at: string;
    ends_at: string;
    component_ids: number[];
  }>({
    title: mw.title,
    description: mw.description ?? "",
    scheduled_at: isoDatetimeToLocalInputValue(mw.scheduled_at),
    ends_at: isoDatetimeToLocalInputValue(mw.ends_at),
    component_ids: mw.components?.map((c) => c.id) ?? [],
  });

  function submit(e: FormEvent) {
    e.preventDefault();

    transform((currentData) => ({
      ...currentData,
      scheduled_at: localDatetimeToUtcIso(currentData.scheduled_at),
      ends_at: localDatetimeToUtcIso(currentData.ends_at),
    }));

    put(update.url({ site, maintenanceWindow: mw }));
  }

  function handleDelete() {
    setDeleting(true);
    router.delete(destroy.url({ site, maintenanceWindow: mw }));
  }

  return (
    <>
      <Head title={`Edit Maintenance — ${site.name}`} />

      <div className="space-y-6">
        <h1 className="font-semibold text-2xl tracking-tight">
          Edit Maintenance Window
        </h1>

        <Card>
          <CardHeader>
            <CardTitle>Maintenance Details</CardTitle>
            <CardDescription>
              Update the scheduled maintenance window.
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

                  <Field data-invalid={!!errors.description}>
                    <FieldLabel htmlFor="description">
                      Description (optional)
                    </FieldLabel>
                    <Textarea
                      aria-invalid={!!errors.description}
                      id="description"
                      onChange={(e) => setData("description", e.target.value)}
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

              <div className="flex items-center justify-between gap-3">
                <Button disabled={processing} type="submit">
                  {processing ? "Saving..." : "Save Changes"}
                </Button>

                <AlertDialog>
                  <AlertDialogTrigger asChild>
                    <Button type="button" variant="destructive">
                      <Trash2Icon />
                      Delete
                    </Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>
                        Delete Maintenance Window
                      </AlertDialogTitle>
                      <AlertDialogDescription>
                        Are you sure you want to delete this maintenance window?
                        This action cannot be undone.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction
                        disabled={deleting}
                        onClick={handleDelete}
                      >
                        Delete
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

MaintenanceEdit.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
