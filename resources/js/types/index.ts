export type * from "./auth";

import type { InertiaLinkProps } from "@inertiajs/react";
import type { LucideIcon } from "lucide-react";
import type { Auth } from "./auth";

export type NavItem = {
  title: string;
  href: NonNullable<InertiaLinkProps["href"]>;
  icon?: LucideIcon | null;
};

export type SharedData = {
  name: string;
  support: {
    admin_email: string | null;
  };
  auth: Auth;
  [key: string]: unknown;
};
