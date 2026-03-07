import type { ComponentProps } from "react";
import AuthSplit from "@/layouts/auth/auth-split";

export type { AuthLayoutProps } from "@/layouts/auth/auth-split";

export default function AuthLayout(props: ComponentProps<typeof AuthSplit>) {
  return <AuthSplit {...props} />;
}
