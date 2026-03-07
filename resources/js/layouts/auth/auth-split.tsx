import type { ReactNode } from "react";
import { AuthLogo } from "@/components/auth/auth-logo";

export interface AuthLayoutProps {
  title: string;
  description: ReactNode;
  children: ReactNode;
}

export default function AuthSplit({
  title,
  description,
  children,
}: AuthLayoutProps) {
  return (
    <div className="flex min-h-screen">
      <div className="flex flex-1 flex-col items-center justify-center p-6 lg:p-10">
        <div className="w-full max-w-sm">
          <div className="mb-6 flex flex-col items-center gap-2 text-center">
            <AuthLogo />
            <h1 className="font-semibold text-xl tracking-tight">{title}</h1>
            <p className="text-muted-foreground text-sm">{description}</p>
          </div>
          {children}
        </div>
      </div>
      <div className="relative hidden flex-1 lg:block">
        <div className="absolute inset-0 bg-muted">
          <div className="flex h-full items-center justify-center">
            <div className="max-w-xs text-center">
              <div className="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-primary/10">
                <AuthLogo />
              </div>
              <p className="text-muted-foreground text-sm">
                Secure, fast, and built for modern teams.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
