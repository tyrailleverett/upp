import { Head, router, useForm } from "@inertiajs/react";
import { type FormEvent, useState } from "react";
import {
  destroy,
  index,
  show,
  update,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { VisibilityBadge } from "@/components/sites/visibility-badge";
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
import type { Site } from "@/types/models";

type Props = {
  site: Site;
};

export default function SitesEdit({ site }: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: index.url() },
    { label: site.name, href: show.url(site) },
    { label: "Edit", href: update.url(site) },
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
    name: site.name,
    slug: site.slug,
    description: site.description ?? "",
    accent_color: site.accent_color ?? "",
    meta_title: site.meta_title ?? "",
    meta_description: site.meta_description ?? "",
    custom_css: site.custom_css ?? "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    put(update.url(site));
  }

  return (
    <>
      <Head title={`Edit ${site.name}`} />

      <div className="space-y-6">
        <div className="flex items-center gap-3">
          <h1 className="font-semibold text-2xl tracking-tight">
            Edit {site.name}
          </h1>
          <VisibilityBadge visibility={site.visibility} />
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Site Details</CardTitle>
            <CardDescription>Update your site information.</CardDescription>
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

                  <Field data-invalid={!!errors.slug}>
                    <FieldLabel htmlFor="slug">Slug</FieldLabel>
                    <Input
                      aria-invalid={!!errors.slug}
                      id="slug"
                      onChange={(e) => setData("slug", e.target.value)}
                      value={data.slug}
                    />
                    <FieldDescription>
                      Only letters, numbers, hyphens, and underscores.
                    </FieldDescription>
                    <FieldError>{errors.slug}</FieldError>
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

        <Card>
          <CardHeader>
            <CardTitle>Branding & SEO</CardTitle>
            <CardDescription>
              Customize the appearance and metadata of your status page.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form
              className="space-y-6"
              onSubmit={(e) => {
                e.preventDefault();
                put(update.url(site));
              }}
            >
              <FieldSet>
                <FieldGroup>
                  <Field data-invalid={!!errors.accent_color}>
                    <FieldLabel htmlFor="accent_color">
                      Accent Color{" "}
                      <span className="font-normal text-muted-foreground">
                        (optional)
                      </span>
                    </FieldLabel>
                    <div className="flex items-center gap-2">
                      <Input
                        aria-invalid={!!errors.accent_color}
                        className="font-mono"
                        id="accent_color"
                        maxLength={7}
                        onChange={(e) =>
                          setData("accent_color", e.target.value)
                        }
                        placeholder="#3B82F6"
                        value={data.accent_color}
                      />
                      {data.accent_color && (
                        <div
                          className="size-9 shrink-0 rounded-md border"
                          style={{ backgroundColor: data.accent_color }}
                        />
                      )}
                    </div>
                    <FieldDescription>Hex color, e.g. #3B82F6</FieldDescription>
                    <FieldError>{errors.accent_color}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.meta_title}>
                    <FieldLabel htmlFor="meta_title">
                      Meta Title{" "}
                      <span className="font-normal text-muted-foreground">
                        (optional)
                      </span>
                    </FieldLabel>
                    <Input
                      aria-invalid={!!errors.meta_title}
                      id="meta_title"
                      onChange={(e) => setData("meta_title", e.target.value)}
                      value={data.meta_title}
                    />
                    <FieldError>{errors.meta_title}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.meta_description}>
                    <FieldLabel htmlFor="meta_description">
                      Meta Description{" "}
                      <span className="font-normal text-muted-foreground">
                        (optional)
                      </span>
                    </FieldLabel>
                    <Textarea
                      aria-invalid={!!errors.meta_description}
                      id="meta_description"
                      onChange={(e) =>
                        setData("meta_description", e.target.value)
                      }
                      value={data.meta_description}
                    />
                    <FieldError>{errors.meta_description}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.custom_css}>
                    <FieldLabel htmlFor="custom_css">
                      Custom CSS{" "}
                      <span className="font-normal text-muted-foreground">
                        (optional)
                      </span>
                    </FieldLabel>
                    <Textarea
                      aria-invalid={!!errors.custom_css}
                      className="font-mono text-xs"
                      id="custom_css"
                      onChange={(e) => setData("custom_css", e.target.value)}
                      rows={8}
                      value={data.custom_css}
                    />
                    <FieldError>{errors.custom_css}</FieldError>
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

        <DeleteSiteCard site={site} />
      </div>
    </>
  );
}

function DeleteSiteCard({ site }: { site: Site }) {
  const [open, setOpen] = useState(false);

  function handleDelete() {
    router.delete(destroy.url(site), {
      onSuccess: () => setOpen(false),
    });
  }

  return (
    <Card className="border-destructive/50">
      <CardHeader>
        <CardTitle>Delete Site</CardTitle>
        <CardDescription>
          Permanently delete this site, its components, and all history. This
          action cannot be undone.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <AlertDialog onOpenChange={setOpen} open={open}>
          <AlertDialogTrigger asChild>
            <Button variant="destructive">Delete site</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete "{site.name}"?</AlertDialogTitle>
              <AlertDialogDescription>
                This will permanently delete the site and all its data. This
                action cannot be undone.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction
                className="bg-destructive text-white hover:bg-destructive/90"
                onClick={handleDelete}
              >
                Delete site
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </CardContent>
    </Card>
  );
}

SitesEdit.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
