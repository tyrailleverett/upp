import { Head, Link } from "@inertiajs/react";
import {
  BanIcon,
  HardDriveIcon,
  type LucideIcon,
  SearchXIcon,
  ShieldXIcon,
} from "lucide-react";
import { Button } from "@/components/ui/button";

interface ErrorPageProps {
  status: number;
}

interface ErrorConfig {
  icon: LucideIcon;
  title: string;
  description: string;
}

const errors: Record<number, ErrorConfig> = {
  403: {
    icon: ShieldXIcon,
    title: "Forbidden",
    description: "You don't have permission to access this page.",
  },
  404: {
    icon: SearchXIcon,
    title: "Not Found",
    description: "The page you're looking for doesn't exist.",
  },
  500: {
    icon: BanIcon,
    title: "Server Error",
    description: "Something went wrong on our end. Please try again later.",
  },
  503: {
    icon: HardDriveIcon,
    title: "Service Unavailable",
    description:
      "We're currently performing maintenance. Please check back soon.",
  },
};

export default function ErrorPage({ status }: ErrorPageProps) {
  const { icon: Icon, title, description } = errors[status] ?? errors[500];

  return (
    <>
      <Head title={`${status} - ${title}`} />
      <div className="flex min-h-screen items-center justify-center bg-background p-6">
        <div className="w-full max-w-sm">
          <div className="flex flex-col items-center gap-4 text-center">
            <Icon className="size-12 text-muted-foreground" />
            <p className="font-bold font-mono text-6xl tracking-tighter">
              {status}
            </p>
            <div className="space-y-1">
              <h1 className="font-semibold text-xl tracking-tight">{title}</h1>
              <p className="text-muted-foreground text-sm">{description}</p>
            </div>
            <Button asChild className="mt-2">
              <Link href="/">Go home</Link>
            </Button>
          </div>
        </div>
      </div>
    </>
  );
}

ErrorPage.layout = (page: React.ReactNode) => page;
