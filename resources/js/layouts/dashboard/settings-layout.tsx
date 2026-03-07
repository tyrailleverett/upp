import { Link, usePage } from "@inertiajs/react";
import type { ReactNode } from "react";
import DashboardController from "@/actions/App/Http/Controllers/DashboardController";
import AppearanceController from "@/actions/App/Http/Controllers/Settings/AppearanceController";
import ConnectedAccountsController from "@/actions/App/Http/Controllers/Settings/ConnectedAccountsController";
import ProfileController from "@/actions/App/Http/Controllers/Settings/ProfileController";
import SecurityController from "@/actions/App/Http/Controllers/Settings/SecurityController";
import SupportController from "@/actions/App/Http/Controllers/Settings/SupportController";
import { useSetBreadcrumbs } from "@/hooks/use-breadcrumbs";
import { cn } from "@/lib/utils";

const tabs = [
  { label: "Profile", href: ProfileController.url() },
  { label: "Security", href: SecurityController.url() },
  { label: "Connected Accounts", href: ConnectedAccountsController.url() },
  { label: "Appearance", href: AppearanceController.url() },
  { label: "Support", href: SupportController.url() },
];

export default function SettingsLayout({ children }: { children: ReactNode }) {
  const currentUrl = usePage().url.split("?")[0];

  const activeTab = tabs.find((tab) => currentUrl === tab.href) ?? tabs[0];

  useSetBreadcrumbs([
    { label: "Dashboard", href: DashboardController.url() },
    { label: "Settings", href: ProfileController.url() },
    { label: activeTab.label, href: activeTab.href },
  ]);

  return (
    <div className="w-full max-w-4xl space-y-6">
      <div>
        <h1 className="font-semibold text-2xl tracking-tight">Settings</h1>
        <p className="text-muted-foreground text-sm">
          Manage your profile, security, and application preferences.
        </p>
      </div>

      <div className="flex flex-col gap-6 md:flex-row">
        <nav className="-mx-1 flex gap-1 overflow-x-auto md:w-48 md:shrink-0 md:flex-col md:overflow-x-visible">
          {tabs.map((tab) => (
            <Link
              className={cn(
                "whitespace-nowrap rounded-md px-3 py-2 font-medium text-sm transition-colors",
                currentUrl === tab.href
                  ? "bg-muted text-foreground"
                  : "text-muted-foreground hover:bg-muted/50 hover:text-foreground"
              )}
              href={tab.href}
              key={tab.href}
              preserveScroll
            >
              {tab.label}
            </Link>
          ))}
        </nav>

        <div className="min-w-0 flex-1">{children}</div>
      </div>
    </div>
  );
}
