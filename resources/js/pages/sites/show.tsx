import { Head, Link, router } from "@inertiajs/react";
import { PencilIcon, PlusIcon, Trash2Icon } from "lucide-react";
import { useState } from "react";
import {
  create as createComponent,
  destroy as destroyComponent,
  edit as editComponent,
} from "@/actions/App/Http/Controllers/Sites/ComponentController";
import {
  destroy,
  edit,
  index,
  show,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { ComponentStatusSelect } from "@/components/sites/component-status-select";
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
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Component, Site } from "@/types/models";

type Props = {
  site: Site & { components: Component[] };
};

export default function SitesShow({ site }: Props) {
  useSetBreadcrumbs([
    { label: "Sites", href: index.url() },
    { label: site.name, href: show.url(site) },
  ]);

  const hasGroups = site.components.some((c) => c.group !== null);

  const componentsByGroup = site.components.reduce<Record<string, Component[]>>(
    (acc, component) => {
      const group = component.group ?? "";
      if (!acc[group]) {
        acc[group] = [];
      }
      acc[group].push(component);
      return acc;
    },
    {}
  );

  return (
    <>
      <Head title={site.name} />

      <div className="space-y-6">
        <div className="flex items-center justify-between gap-4">
          <div className="flex min-w-0 items-center gap-3">
            <h1 className="truncate font-semibold text-2xl tracking-tight">
              {site.name}
            </h1>
            <VisibilityBadge visibility={site.visibility} />
          </div>
          <div className="flex shrink-0 items-center gap-2">
            <Button asChild size="sm" variant="outline">
              <Link href={edit.url(site)}>
                <PencilIcon />
                Edit
              </Link>
            </Button>
            <DeleteSiteButton site={site} />
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm">
            <div className="flex gap-2">
              <span className="w-28 shrink-0 text-muted-foreground">Slug</span>
              <span className="font-mono">/{site.slug}</span>
            </div>
            {site.description && (
              <div className="flex gap-2">
                <span className="w-28 shrink-0 text-muted-foreground">
                  Description
                </span>
                <span>{site.description}</span>
              </div>
            )}
            <div className="flex gap-2">
              <span className="w-28 shrink-0 text-muted-foreground">
                Visibility
              </span>
              <VisibilityBadge visibility={site.visibility} />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div className="flex items-center justify-between gap-4">
              <div>
                <CardTitle>Components</CardTitle>
                <CardDescription>
                  The services and systems monitored by this status page.
                </CardDescription>
              </div>
              <Button asChild size="sm">
                <Link href={createComponent.url(site)}>
                  <PlusIcon />
                  Add Component
                </Link>
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            {site.components.length === 0 && (
              <div className="py-8 text-center text-muted-foreground text-sm">
                No components yet. Add one to start monitoring your services.
              </div>
            )}
            {site.components.length > 0 && hasGroups && (
              <div className="space-y-6">
                {Object.entries(componentsByGroup).map(
                  ([group, groupComponents]) => (
                    <div className="space-y-2" key={group}>
                      {group && (
                        <h3 className="font-medium text-muted-foreground text-sm uppercase tracking-wider">
                          {group}
                        </h3>
                      )}
                      <ComponentList components={groupComponents} site={site} />
                    </div>
                  )
                )}
              </div>
            )}
            {site.components.length > 0 && !hasGroups && (
              <ComponentList components={site.components} site={site} />
            )}
          </CardContent>
        </Card>
      </div>
    </>
  );
}

function ComponentList({
  site,
  components,
}: {
  site: Site;
  components: Component[];
}) {
  return (
    <div className="divide-y rounded-md border">
      {components.map((component) => (
        <div className="flex items-center gap-3 px-4 py-3" key={component.id}>
          <div className="min-w-0 flex-1">
            <p className="truncate font-medium text-sm">{component.name}</p>
            <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-muted-foreground text-xs">
              <span>{component.group ?? "Ungrouped"}</span>
              <span>Sort order {component.sort_order}</span>
            </div>
            {component.description && (
              <p className="truncate pt-1 text-muted-foreground text-xs">
                {component.description}
              </p>
            )}
          </div>
          <ComponentStatusSelect component={component} siteSlug={site.slug} />
          <Button asChild size="sm" variant="ghost">
            <Link href={editComponent.url({ site, component })}>
              <PencilIcon className="size-3.5" />
              <span className="sr-only">Edit</span>
            </Link>
          </Button>
          <DeleteComponentButton component={component} site={site} />
        </div>
      ))}
    </div>
  );
}

function DeleteSiteButton({ site }: { site: Site }) {
  const [open, setOpen] = useState(false);

  function handleDelete() {
    router.delete(destroy.url(site), {
      onSuccess: () => setOpen(false),
    });
  }

  return (
    <AlertDialog onOpenChange={setOpen} open={open}>
      <AlertDialogTrigger asChild>
        <Button size="sm" variant="destructive">
          <Trash2Icon />
          Delete
        </Button>
      </AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete "{site.name}"?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently delete the site and all its components and
            history. This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            className="bg-destructive text-white hover:bg-destructive/90"
            onClick={handleDelete}
          >
            Delete Site
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}

function DeleteComponentButton({
  site,
  component,
}: {
  site: Site;
  component: Component;
}) {
  const [open, setOpen] = useState(false);

  function handleDelete() {
    router.delete(destroyComponent.url({ site, component }), {
      onSuccess: () => setOpen(false),
      preserveScroll: true,
    });
  }

  return (
    <AlertDialog onOpenChange={setOpen} open={open}>
      <AlertDialogTrigger asChild>
        <Button size="sm" variant="ghost">
          <Trash2Icon className="size-3.5 text-destructive" />
          <span className="sr-only">Delete</span>
        </Button>
      </AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete "{component.name}"?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently delete the component and all its status
            history. This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            className="bg-destructive text-white hover:bg-destructive/90"
            onClick={handleDelete}
          >
            Delete Component
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}

SitesShow.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
