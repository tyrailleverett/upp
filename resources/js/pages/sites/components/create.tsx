import { Head, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import { store } from "@/actions/App/Http/Controllers/Sites/ComponentController";
import {
  index,
  show,
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
import type { Site } from "@/types/models";

type Props = {
  site: Site;
};

export default function ComponentsCreate({ site }: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: index.url() },
    { label: site.name, href: show.url(site) },
    { label: "Add Component", href: store.url(site) },
  ]);

  const { data, setData, post, processing, errors } = useForm({
    name: "",
    description: "",
    group: "",
    sort_order: 0,
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url(site));
  }

  return (
    <>
      <Head title={`Add Component — ${site.name}`} />

      <div className="space-y-6">
        <h1 className="font-semibold text-2xl tracking-tight">Add Component</h1>

        <Card>
          <CardHeader>
            <CardTitle>Component Details</CardTitle>
            <CardDescription>
              Add a new service or system to "{site.name}".
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
                      placeholder="API"
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
                      placeholder="A brief description of this component..."
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
                      placeholder="Core Services"
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
                      Lower numbers appear first. Defaults to 0.
                    </FieldDescription>
                    <FieldError>{errors.sort_order}</FieldError>
                  </Field>
                </FieldGroup>
              </FieldSet>

              <div className="flex items-center gap-3">
                <Button disabled={processing} type="submit">
                  {processing ? "Adding..." : "Add Component"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </>
  );
}

ComponentsCreate.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
