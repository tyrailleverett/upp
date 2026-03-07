import { Head, router, useForm } from "@inertiajs/react";
import { TicketIcon } from "lucide-react";
import type { FormEvent, MouseEvent } from "react";
import { store } from "@/actions/App/Http/Controllers/Support/SupportTicketController";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Empty,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from "@/components/ui/empty";
import {
  Field,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Textarea } from "@/components/ui/textarea";
import DashboardLayout from "@/layouts/dashboard/layout";
import SettingsLayout from "@/layouts/dashboard/settings-layout";

type Ticket = {
  id: number;
  title: string;
  description: string;
  topic: string;
  resolution: string | null;
  created_at: string;
};

type Props = {
  tickets: {
    data: Ticket[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
  };
};

const topicLabels: Record<string, string> = {
  general: "General",
  technical: "Technical",
  account: "Account",
  feature_request: "Feature Request",
  bug_report: "Bug Report",
};

const topicOptions = [
  { value: "general", label: "General" },
  { value: "technical", label: "Technical" },
  { value: "account", label: "Account" },
  { value: "feature_request", label: "Feature Request" },
  { value: "bug_report", label: "Bug Report" },
];

export default function Support({ tickets }: Props) {
  const { data, setData, post, processing, errors, reset } = useForm({
    title: "",
    description: "",
    topic: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url(), {
      onSuccess: () => reset(),
    });
  }

  function handlePaginationClick(
    e: MouseEvent<HTMLAnchorElement>,
    url: string | null
  ) {
    if (url === null) {
      e.preventDefault();

      return;
    }

    e.preventDefault();

    router.visit(url, {
      only: ["tickets"],
      preserveScroll: true,
      preserveState: true,
    });
  }

  return (
    <>
      <Head title="Support" />

      <div className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Submit a Ticket</CardTitle>
            <CardDescription>
              Describe your issue or request and we'll get back to you.
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
                      placeholder="Brief summary of your issue"
                      value={data.title}
                    />
                    <FieldError>{errors.title}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.topic}>
                    <FieldLabel htmlFor="topic">Topic</FieldLabel>
                    <Select
                      onValueChange={(value) => setData("topic", value)}
                      value={data.topic}
                    >
                      <SelectTrigger
                        aria-invalid={!!errors.topic}
                        className="w-full"
                        id="topic"
                      >
                        <SelectValue placeholder="Select a topic" />
                      </SelectTrigger>
                      <SelectContent>
                        {topicOptions.map((option) => (
                          <SelectItem key={option.value} value={option.value}>
                            {option.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FieldError>{errors.topic}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.description}>
                    <FieldLabel htmlFor="description">Description</FieldLabel>
                    <Textarea
                      aria-invalid={!!errors.description}
                      id="description"
                      onChange={(e) => setData("description", e.target.value)}
                      placeholder="Describe your issue in detail..."
                      rows={4}
                      value={data.description}
                    />
                    <FieldError>{errors.description}</FieldError>
                  </Field>
                </FieldGroup>
              </FieldSet>

              <div className="flex items-center gap-3">
                <Button disabled={processing} type="submit">
                  {processing ? "Submitting..." : "Submit ticket"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Your Tickets</CardTitle>
            <CardDescription>
              View the status of your submitted support tickets.
            </CardDescription>
          </CardHeader>
          <CardContent>
            {tickets.data.length > 0 ? (
              <>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Title</TableHead>
                      <TableHead>Topic</TableHead>
                      <TableHead>Date</TableHead>
                      <TableHead>Resolution</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {tickets.data.map((ticket) => (
                      <TableRow key={ticket.id}>
                        <TableCell className="font-medium">
                          {ticket.title}
                        </TableCell>
                        <TableCell>
                          <Badge variant="secondary">
                            {topicLabels[ticket.topic] ?? ticket.topic}
                          </Badge>
                        </TableCell>
                        <TableCell className="whitespace-nowrap text-muted-foreground">
                          {new Date(ticket.created_at).toLocaleDateString()}
                        </TableCell>
                        <TableCell className="whitespace-nowrap text-muted-foreground">
                          {ticket.resolution ?? "Pending"}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>

                {tickets.last_page > 1 ? (
                  <Pagination className="mt-4 justify-between">
                    <p className="text-muted-foreground text-sm">
                      Page {tickets.current_page} of {tickets.last_page}
                    </p>
                    <PaginationContent>
                      <PaginationItem>
                        <PaginationPrevious
                          className={
                            tickets.prev_page_url === null
                              ? "pointer-events-none opacity-50"
                              : undefined
                          }
                          href={tickets.prev_page_url ?? "#"}
                          onClick={(e) =>
                            handlePaginationClick(e, tickets.prev_page_url)
                          }
                        />
                      </PaginationItem>
                      <PaginationItem>
                        <PaginationNext
                          className={
                            tickets.next_page_url === null
                              ? "pointer-events-none opacity-50"
                              : undefined
                          }
                          href={tickets.next_page_url ?? "#"}
                          onClick={(e) =>
                            handlePaginationClick(e, tickets.next_page_url)
                          }
                        />
                      </PaginationItem>
                    </PaginationContent>
                  </Pagination>
                ) : null}
              </>
            ) : (
              <Empty>
                <EmptyHeader>
                  <EmptyMedia variant="icon">
                    <TicketIcon />
                  </EmptyMedia>
                  <EmptyTitle>No tickets yet</EmptyTitle>
                  <EmptyDescription>
                    When you submit a support ticket, it will appear here.
                  </EmptyDescription>
                </EmptyHeader>
              </Empty>
            )}
          </CardContent>
        </Card>
      </div>
    </>
  );
}

Support.layout = (page: React.ReactNode) => (
  <DashboardLayout>
    <SettingsLayout>{page}</SettingsLayout>
  </DashboardLayout>
);
