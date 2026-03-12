import { Head, Link, router, useForm } from "@inertiajs/react";
import { PencilIcon, Trash2Icon } from "lucide-react";
import { type FormEvent, useState } from "react";
import {
  destroy,
  edit,
  index,
  show,
} from "@/actions/App/Http/Controllers/Sites/IncidentController";
import {
  resolve,
  store as storeUpdate,
} from "@/actions/App/Http/Controllers/Sites/IncidentUpdateController";
import {
  index as siteIndex,
  show as siteShow,
} from "@/actions/App/Http/Controllers/Sites/SiteController";
import { ComponentStatusBadge } from "@/components/sites/component-status-badge";
import { IncidentStatusBadge } from "@/components/sites/incident-status-badge";
import { IncidentTimeline } from "@/components/sites/incident-timeline";
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import DashboardLayout from "@/layouts/dashboard/layout";
import type {
  Component,
  Incident,
  IncidentStatus,
  IncidentUpdate,
  Site,
} from "@/types/models";

type Props = {
  site: Site;
  incident: Incident & { updates: IncidentUpdate[]; components: Component[] };
  siteComponents: Component[];
};

const incidentStatusOptions: { value: IncidentStatus; label: string }[] = [
  { value: "investigating", label: "Investigating" },
  { value: "identified", label: "Identified" },
  { value: "monitoring", label: "Monitoring" },
  { value: "resolved", label: "Resolved" },
];

export default function IncidentsShow({
  site,
  incident,
  // siteComponents is passed for potential future use (e.g., editing affected components inline)
  siteComponents: _siteComponents,
}: Props) {
  const impactStatus = getImpactStatus(incident.components);

  useSetBreadcrumbs([
    { label: "Sites", href: siteIndex.url() },
    { label: site.name, href: siteShow.url(site) },
    { label: "Incidents", href: index.url(site) },
    { label: incident.title, href: show.url({ site, incident }) },
  ]);

  return (
    <>
      <Head title={`${incident.title} — ${site.name}`} />

      <div className="space-y-6">
        <div className="flex items-center justify-between gap-4">
          <div className="flex min-w-0 items-center gap-3">
            <h1 className="truncate font-semibold text-2xl tracking-tight">
              {incident.title}
            </h1>
            <IncidentStatusBadge status={incident.status} />
          </div>
          <div className="flex shrink-0 items-center gap-2">
            <Button asChild size="sm" variant="outline">
              <Link href={edit.url({ site, incident })}>
                <PencilIcon />
                Edit
              </Link>
            </Button>
            <DeleteIncidentButton incident={incident} site={site} />
          </div>
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div className="space-y-6 lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle>Timeline</CardTitle>
              </CardHeader>
              <CardContent>
                <IncidentTimeline updates={incident.updates} />
              </CardContent>
            </Card>

            {!incident.resolved_at && (
              <PostUpdateForm incident={incident} site={site} />
            )}

            {!incident.resolved_at && (
              <ResolveIncidentForm incident={incident} site={site} />
            )}

            {incident.resolved_at && (
              <Card>
                <CardHeader>
                  <CardTitle>Resolved</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-sm">
                  <div className="flex gap-2">
                    <span className="w-28 shrink-0 text-muted-foreground">
                      Resolved at
                    </span>
                    <span>
                      {new Date(incident.resolved_at).toLocaleString()}
                    </span>
                  </div>
                  {incident.postmortem && (
                    <div className="space-y-1">
                      <p className="font-medium text-muted-foreground">
                        Postmortem
                      </p>
                      <p className="whitespace-pre-wrap">
                        {incident.postmortem}
                      </p>
                    </div>
                  )}
                </CardContent>
              </Card>
            )}
          </div>

          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3 text-sm">
                <div className="flex gap-2">
                  <span className="w-20 shrink-0 text-muted-foreground">
                    Status
                  </span>
                  <IncidentStatusBadge status={incident.status} />
                </div>
                <div className="flex gap-2">
                  <span className="w-20 shrink-0 text-muted-foreground">
                    Site
                  </span>
                  <span>{site.name}</span>
                </div>
                <div className="flex gap-2">
                  <span className="w-20 shrink-0 text-muted-foreground">
                    Impact
                  </span>
                  {impactStatus ? (
                    <ComponentStatusBadge status={impactStatus} />
                  ) : (
                    <span>Unknown</span>
                  )}
                </div>
                <div className="flex gap-2">
                  <span className="w-20 shrink-0 text-muted-foreground">
                    Started
                  </span>
                  <span>{new Date(incident.created_at).toLocaleString()}</span>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Affected Components</CardTitle>
              </CardHeader>
              <CardContent>
                {incident.components.length === 0 ? (
                  <p className="text-muted-foreground text-sm">
                    No components affected.
                  </p>
                ) : (
                  <div className="space-y-2">
                    {incident.components.map((component) => (
                      <div
                        className="flex items-center justify-between gap-3 rounded-lg border px-3 py-2"
                        key={component.id}
                      >
                        <span className="font-medium text-sm">
                          {component.name}
                        </span>
                        <ComponentStatusBadge status={component.status} />
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </>
  );
}

function PostUpdateForm({
  incident,
  site,
}: {
  incident: Incident;
  site: Site;
}) {
  const { data, setData, post, processing, errors, reset } = useForm<{
    status: IncidentStatus;
    message: string;
  }>({
    status: incident.status as IncidentStatus,
    message: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(storeUpdate.url({ site, incident }), {
      onSuccess: () => reset(),
    });
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Post Update</CardTitle>
        <CardDescription>Add a new update to the timeline.</CardDescription>
      </CardHeader>
      <CardContent>
        <form className="space-y-4" onSubmit={submit}>
          <FieldSet>
            <FieldGroup>
              <Field data-invalid={!!errors.status}>
                <FieldLabel htmlFor="update-status">Status</FieldLabel>
                <Select
                  onValueChange={(v) => setData("status", v as IncidentStatus)}
                  value={data.status}
                >
                  <SelectTrigger
                    aria-invalid={!!errors.status}
                    id="update-status"
                  >
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    {incidentStatusOptions.map((option) => (
                      <SelectItem key={option.value} value={option.value}>
                        {option.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <FieldError>{errors.status}</FieldError>
              </Field>

              <Field data-invalid={!!errors.message}>
                <FieldLabel htmlFor="update-message">Message</FieldLabel>
                <Textarea
                  aria-invalid={!!errors.message}
                  id="update-message"
                  onChange={(e) => setData("message", e.target.value)}
                  placeholder="Describe the latest status..."
                  required
                  rows={3}
                  value={data.message}
                />
                <FieldError>{errors.message}</FieldError>
              </Field>
            </FieldGroup>
          </FieldSet>

          <Button disabled={processing} type="submit">
            {processing ? "Posting..." : "Post Update"}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}

function ResolveIncidentForm({
  incident,
  site,
}: {
  incident: Incident;
  site: Site;
}) {
  const { data, setData, post, processing, errors } = useForm<{
    message: string;
    postmortem: string;
  }>({
    message: "",
    postmortem: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(resolve.url({ site, incident }));
  }

  return (
    <Card className="border-green-500/30">
      <CardHeader>
        <CardTitle>Resolve Incident</CardTitle>
        <CardDescription>
          Mark this incident as resolved and optionally add a postmortem.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form className="space-y-4" onSubmit={submit}>
          <FieldSet>
            <FieldGroup>
              <Field data-invalid={!!errors.message}>
                <FieldLabel htmlFor="resolve-message">
                  Resolution Message
                </FieldLabel>
                <Textarea
                  aria-invalid={!!errors.message}
                  id="resolve-message"
                  onChange={(e) => setData("message", e.target.value)}
                  placeholder="Describe how the incident was resolved..."
                  required
                  rows={3}
                  value={data.message}
                />
                <FieldError>{errors.message}</FieldError>
              </Field>

              <Field data-invalid={!!errors.postmortem}>
                <FieldLabel htmlFor="postmortem">
                  Postmortem{" "}
                  <span className="font-normal text-muted-foreground">
                    (optional)
                  </span>
                </FieldLabel>
                <Textarea
                  aria-invalid={!!errors.postmortem}
                  id="postmortem"
                  onChange={(e) => setData("postmortem", e.target.value)}
                  placeholder="Root cause analysis, timeline, and lessons learned..."
                  rows={5}
                  value={data.postmortem}
                />
                <FieldError>{errors.postmortem}</FieldError>
              </Field>
            </FieldGroup>
          </FieldSet>

          <Button disabled={processing} type="submit" variant="default">
            {processing ? "Resolving..." : "Resolve Incident"}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}

function DeleteIncidentButton({
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
    <AlertDialog onOpenChange={setOpen} open={open}>
      <AlertDialogTrigger asChild>
        <Button size="sm" variant="destructive">
          <Trash2Icon />
          Delete
        </Button>
      </AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete this incident?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently delete the incident and all its timeline
            updates. This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            className="bg-destructive text-white hover:bg-destructive/90"
            onClick={handleDelete}
          >
            Delete Incident
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}

function getImpactStatus(components: Component[]): Component["status"] | null {
  return components.reduce<Component["status"] | null>((highest, component) => {
    if (highest === null) {
      return component.status;
    }

    return componentStatusSeverity[component.status] >
      componentStatusSeverity[highest]
      ? component.status
      : highest;
  }, null);
}

const componentStatusSeverity: Record<Component["status"], number> = {
  operational: 0,
  degraded_performance: 1,
  partial_outage: 2,
  under_maintenance: 3,
  major_outage: 4,
};

IncidentsShow.layout = (page: React.ReactNode) => (
  <DashboardLayout>{page}</DashboardLayout>
);
