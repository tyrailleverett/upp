import { Head, Link } from "@inertiajs/react";
import { PlusIcon } from "lucide-react";
import {
  create,
  index,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { SiteCard } from "@/components/sites/site-card";
import { Button } from "@/components/ui/button";
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from "@/components/ui/empty";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type { Site } from "@/types/models";

type Props = {
  sites: Site[];
};

export default function SitesIndex({ sites }: Props) {
  useSetBreadcrumbs([{ label: "Sites", href: index.url() }]);

  return (
    <>
      <Head title="Sites" />

      <div className="space-y-6">
        <div className="flex items-center justify-between gap-4">
          <h1 className="font-semibold text-2xl tracking-tight">Sites</h1>
          <Button asChild>
            <Link href={create.url()}>
              <PlusIcon />
              Create Site
            </Link>
          </Button>
        </div>

        {sites.length === 0 ? (
          <Empty>
            <EmptyHeader>
              <EmptyMedia variant="icon">
                <PlusIcon />
              </EmptyMedia>
              <EmptyTitle>No sites yet</EmptyTitle>
              <EmptyDescription>
                Create your first status page to start monitoring your services.
              </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
              <Button asChild>
                <Link href={create.url()}>
                  <PlusIcon />
                  Create your first site
                </Link>
              </Button>
            </EmptyContent>
          </Empty>
        ) : (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {sites.map((site) => (
              <SiteCard key={site.id} site={site} />
            ))}
          </div>
        )}
      </div>
    </>
  );
}

SitesIndex.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
