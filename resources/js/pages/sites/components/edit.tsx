import { Head, router, useForm } from "@inertiajs/react";
import { type FormEvent, useState } from "react";
import {
  destroy,
  update,
} from "@/actions/App/Http/Controllers/Sites/ComponentController";
import {
  index,
  show,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
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
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Component, Site } from "@/types/models";

type Props = {
  site: Site;
  component: Component;
};

export default function ComponentsEdit({ site, component }: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: index.url() },
    { label: site.name, href: show.url(site) },
    { label: `Edit ${component.name}`, href: update.url({ site, component }) },
  ]);

  const {
    data,
    setData,
    put,
    processing,
    errors,
    recentlySuccessful,
    isDirty,
  } = useForm({
    name: component.name,
    description: component.description ?? "",
    group: component.group ?? "",
    sort_order: component.sort_order,
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    put(update.url({ site, component }));
  }

  return (
    <>
      <Head title={`Edit ${component.name}`} />

      <div className="space-y-6">
        <h1 className="font-semibold text-2xl tracking-tight">
          Edit {component.name}
        </h1>

        <Card>
          <CardHeader>
            <CardTitle>Component Details</CardTitle>
            <CardDescription>
              Update the details for this component.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form className="space-y-6" onSubmit={submit}>
              <FieldSet>
                <FieldGroup>
                  <Field data-invalid={!!errors.name}>
                    <FieldLabel htmlFor="name">Name</FieldLabel>
                    <Input
                      aria-invalid={!!errors.name}
                      id="name"
                      onChange={(e) => setData("name", e.target.value)}
                      value={data.name}
                    />
                    <FieldError>{errors.name}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.description}>
                    <FieldLabel htmlFor="description">
                      Description{" "}
                      <span className="font-normal text-muted-foreground">
                        (optional)
                      </span>
                    </FieldLabel>
                    <Textarea
                      aria-invalid={!!errors.description}
                      id="description"
                      onChange={(e) => setData("description", e.target.value)}
                      value={data.description}
                    />
                    <FieldError>{errors.description}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.group}>
                    <FieldLabel htmlFor="group">
                      Group{" "}
                      <span className="font-normal text-muted-foreground">
                        (optional)
                      </span>
                    </FieldLabel>
                    <Input
                      aria-invalid={!!errors.group}
                      id="group"
                      onChange={(e) => setData("group", e.target.value)}
                      value={data.group}
                    />
                    <FieldDescription>
                      Group related components together on your status page.
                    </FieldDescription>
                    <FieldError>{errors.group}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.sort_order}>
                    <FieldLabel htmlFor="sort_order">Sort Order</FieldLabel>
                    <Input
                      aria-invalid={!!errors.sort_order}
                      id="sort_order"
                      min={0}
                      onChange={(e) =>
                        setData("sort_order", Number(e.target.value))
                      }
                      type="number"
                      value={data.sort_order}
                    />
                    <FieldDescription>
                      Lower numbers appear first.
                    </FieldDescription>
                    <FieldError>{errors.sort_order}</FieldError>
                  </Field>
                </FieldGroup>
              </FieldSet>

              <div className="flex items-center gap-3">
                <Button disabled={processing || !isDirty} type="submit">
                  {processing ? "Saving..." : "Save changes"}
                </Button>
                {recentlySuccessful && (
                  <p className="text-muted-foreground text-sm">Saved.</p>
                )}
              </div>
            </form>
          </CardContent>
        </Card>

        <DeleteComponentCard component={component} site={site} />
      </div>
    </>
  );
}

function DeleteComponentCard({
  site,
  component,
}: {
  site: Site;
  component: Component;
}) {
  const [open, setOpen] = useState(false);

  function handleDelete() {
    router.delete(destroy.url({ site, component }), {
      onSuccess: () => setOpen(false),
    });
  }

  return (
    <Card className="border-destructive/50">
      <CardHeader>
        <CardTitle>Delete Component</CardTitle>
        <CardDescription>
          Permanently delete this component and all its status history. This
          action cannot be undone.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <AlertDialog onOpenChange={setOpen} open={open}>
          <AlertDialogTrigger asChild>
            <Button variant="destructive">Delete component</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete "{component.name}"?</AlertDialogTitle>
              <AlertDialogDescription>
                This will permanently delete the component and all its data.
                This action cannot be undone.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction
                className="bg-destructive text-white hover:bg-destructive/90"
                onClick={handleDelete}
              >
                Delete component
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </CardContent>
    </Card>
  );
}

ComponentsEdit.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
