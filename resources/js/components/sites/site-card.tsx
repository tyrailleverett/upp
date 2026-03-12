import { Link } from "@inertiajs/react";
import { show } from "@/actions/App/Http/Controllers/Sites/SiteController";
import { VisibilityBadge } from "@/components/sites/visibility-badge";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import type { Site } from "@/types/models";

type Props = {
  site: Site;
};

export function SiteCard({ site }: Props) {
  const createdAt = new Date(site.created_at).toLocaleDateString(undefined, {
    month: "short",
    day: "numeric",
    year: "numeric",
  });

  return (
    <Link className="group block" href={show.url(site)}>
      <Card className="h-full transition-colors group-hover:border-ring/50">
        <CardHeader>
          <div className="flex items-start justify-between gap-2">
            <CardTitle className="truncate text-base">{site.name}</CardTitle>
            <VisibilityBadge visibility={site.visibility} />
          </div>
          <CardDescription className="truncate">/{site.slug}</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-1.5 text-muted-foreground text-sm">
            <p>
              {site.components_count ?? 0}{" "}
              {site.components_count === 1 ? "component" : "components"}
            </p>
            <p>Created {createdAt}</p>
          </div>
        </CardContent>
      </Card>
    </Link>
  );
}
