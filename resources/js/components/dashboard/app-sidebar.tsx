import { Link, usePage } from "@inertiajs/react";
import { LayoutGrid } from "lucide-react";
import DashboardController from "@/actions/App/Http/Controllers/DashboardController";
import AppLogo from "@/components/app/app-logo";
import { NavUser } from "@/components/dashboard/nav-user";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import { resolveUrl } from "@/lib/utils";
import type { NavItem } from "@/types";

const mainNavItems: NavItem[] = [
  {
    title: "Dashboard",
    href: DashboardController.url(),
    icon: LayoutGrid,
  },
];

export function AppSidebar() {
  const page = usePage();

  return (
    <Sidebar collapsible="icon" variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton asChild size="lg">
              <Link href={DashboardController.url()}>
                <AppLogo />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        <SidebarGroup className="px-2 py-0">
          <SidebarGroupLabel>Platform</SidebarGroupLabel>
          <SidebarMenu>
            {mainNavItems.map((item) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton
                  asChild
                  isActive={page.url.startsWith(resolveUrl(item.href))}
                  tooltip={{ children: item.title }}
                >
                  <Link href={item.href}>
                    {item.icon && <item.icon />}
                    <span>{item.title}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}
          </SidebarMenu>
        </SidebarGroup>
      </SidebarContent>

      <SidebarFooter>
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
