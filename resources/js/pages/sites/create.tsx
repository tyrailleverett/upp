import { Head, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import {
  index,
  store,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
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

export default function SitesCreate() {
  useSetBreadcrumbs([
    { label: "Sites", href: index.url() },
    { label: "Create Site", href: store.url() },
  ]);

  const { data, setData, post, processing, errors } = useForm({
    name: "",
    slug: "",
    description: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url());
  }

  return (
    <>
      <Head title="Create Site" />

      <div className="space-y-6">
        <h1 className="font-semibold text-2xl tracking-tight">Create Site</h1>

        <Card>
          <CardHeader>
            <CardTitle>Site Details</CardTitle>
            <CardDescription>
              Set up a new status page for your services.
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
                      placeholder="My Status Page"
                      required
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
                      placeholder="my-status-page"
                      required
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
                      placeholder="A brief description of this status page..."
                      value={data.description}
                    />
                    <FieldError>{errors.description}</FieldError>
                  </Field>
                </FieldGroup>
              </FieldSet>

              <div className="flex items-center gap-3">
                <Button disabled={processing} type="submit">
                  {processing ? "Creating..." : "Create Site"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

SitesCreate.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
