import { Link, router, usePage } from "@inertiajs/react";
import {
  CreditCardIcon,
  LayoutGrid,
  LifeBuoyIcon,
  LogOutIcon,
  Menu,
  SettingsIcon,
} from "lucide-react";
import LogoutController from "@/actions/App/Http/Controllers/Auth/LogoutController";
import DashboardController from "@/actions/App/Http/Controllers/DashboardController";
import BillingController from "@/actions/App/Http/Controllers/Settings/BillingController";
import ProfileController from "@/actions/App/Http/Controllers/Settings/ProfileController";
import SupportController from "@/actions/App/Http/Controllers/Settings/SupportController";
import { DashboardBreadcrumbs } from "@/components/dashboard/breadcrumbs";
import { Icon } from "@/components/layout/icon";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  NavigationMenu,
  NavigationMenuItem,
  NavigationMenuList,
  navigationMenuTriggerStyle,
} from "@/components/ui/navigation-menu";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import { useBreadcrumbs } from "@/hooks/use-breadcrumbs";
import { useInitials } from "@/hooks/use-initials";
import { useMobileNavigation } from "@/hooks/use-mobile-navigation";
import { cn, isSameUrl } from "@/lib/utils";
import type { NavItem, SharedData } from "@/types";
import AppLogo from "./app-logo";
import AppLogoIcon from "./app-logo-icon";

const mainNavItems: NavItem[] = [
  {
    title: "Dashboard",
    href: DashboardController.url(),
    icon: LayoutGrid,
  },
];

export function AppHeader() {
  const page = usePage<SharedData>();
  const { auth } = page.props;
  const getInitials = useInitials();
  const cleanup = useMobileNavigation();
  const breadcrumbs = useBreadcrumbs();

  return (
    <>
      <div className="border-sidebar-border/80 border-b">
        <div className="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
          {/* Mobile Menu */}
          <div className="lg:hidden">
            <Sheet>
              <SheetTrigger asChild>
                <Button
                  className="mr-2 h-[34px] w-[34px]"
                  size="icon"
                  variant="ghost"
                >
                  <Menu className="h-5 w-5" />
                </Button>
              </SheetTrigger>
              <SheetContent
                className="flex h-full w-64 flex-col items-stretch justify-between bg-sidebar"
                side="left"
              >
                <SheetTitle className="sr-only">Navigation Menu</SheetTitle>
                <SheetHeader className="flex justify-start text-left">
                  <AppLogoIcon className="h-6 w-6 text-black dark:text-white" />
                </SheetHeader>
                <div className="flex h-full flex-1 flex-col space-y-4 p-4">
                  <div className="flex h-full flex-col justify-between text-sm">
                    <div className="flex flex-col space-y-4">
                      {mainNavItems.map((item) => (
                        <Link
                          className="flex items-center space-x-2 font-medium"
                          href={item.href}
                          key={item.title}
                          onClick={cleanup}
                        >
                          {item.icon && (
                            <Icon className="h-5 w-5" iconNode={item.icon} />
                          )}
                          <span>{item.title}</span>
                        </Link>
                      ))}
                    </div>
                  </div>
                </div>
              </SheetContent>
            </Sheet>
          </div>

          <Link
            className="flex items-center space-x-2"
            href={DashboardController.url()}
          >
            <AppLogo />
          </Link>

          {/* Desktop Navigation */}
          <div className="ml-6 hidden h-full items-center space-x-6 lg:flex">
            <NavigationMenu className="flex h-full items-stretch">
              <NavigationMenuList className="flex h-full items-stretch space-x-2">
                {mainNavItems.map((item) => (
                  <NavigationMenuItem
                    className="relative flex h-full items-center"
                    key={item.title}
                  >
                    <Link
                      className={cn(
                        navigationMenuTriggerStyle(),
                        isSameUrl(page.url, item.href) &&
                          "text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100",
                        "h-9 cursor-pointer px-3"
                      )}
                      href={item.href}
                    >
                      {item.icon && (
                        <Icon className="mr-2 h-4 w-4" iconNode={item.icon} />
                      )}
                      {item.title}
                    </Link>
                    {isSameUrl(page.url, item.href) && (
                      <div className="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white" />
                    )}
                  </NavigationMenuItem>
                ))}
              </NavigationMenuList>
            </NavigationMenu>
          </div>

          <div className="ml-auto flex items-center space-x-2">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button className="size-10 rounded-full p-1" variant="ghost">
                  <Avatar className="size-8 overflow-hidden rounded-full">
                    <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                      {getInitials(auth.user.name)}
                    </AvatarFallback>
                  </Avatar>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel className="p-0 font-normal">
                  <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <Avatar className="size-8 overflow-hidden rounded-lg">
                      <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                        {getInitials(auth.user.name)}
                      </AvatarFallback>
                    </Avatar>
                    <div className="grid flex-1 text-left text-sm leading-tight">
                      <span className="truncate font-medium">
                        {auth.user.name}
                      </span>
                      <span className="truncate text-muted-foreground text-xs">
                        {auth.user.email}
                      </span>
                    </div>
                  </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuGroup>
                  <DropdownMenuItem asChild>
                    <Link
                      as="button"
                      className="block w-full"
                      href={ProfileController.url()}
                      onClick={cleanup}
                    >
                      <SettingsIcon className="mr-2" />
                      Settings
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link
                      as="button"
                      className="block w-full"
                      href={BillingController.url()}
                      onClick={cleanup}
                    >
                      <CreditCardIcon className="mr-2" />
                      Billing
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link
                      as="button"
                      className="block w-full"
                      href={SupportController.url()}
                      onClick={cleanup}
                    >
                      <LifeBuoyIcon className="mr-2" />
                      Support
                    </Link>
                  </DropdownMenuItem>
                </DropdownMenuGroup>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                  <Link
                    as="button"
                    className="block w-full"
                    href={LogoutController.url()}
                    onClick={() => {
                      cleanup();
                      router.flushAll();
                    }}
                  >
                    <LogOutIcon className="mr-2" />
                    Log out
                  </Link>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </div>
      {breadcrumbs.length > 1 && (
        <div className="flex w-full border-sidebar-border/70 border-b">
          <div className="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
            <DashboardBreadcrumbs />
          </div>
        </div>
      )}
    </>
  );
}
