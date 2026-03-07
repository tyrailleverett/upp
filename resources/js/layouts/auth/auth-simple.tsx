import type { ReactNode } from "react";
import { AuthLogo } from "@/components/auth/auth-logo";

export interface AuthLayoutProps {
  title: string;
  description: ReactNode;
  children: ReactNode;
}

export default function AuthSimple({
  title,
  description,
  children,
}: AuthLayoutProps) {
  return (
    <div className="flex min-h-screen items-center justify-center bg-background p-6">
      <div className="w-full max-w-sm">
        <div className="mb-6 flex flex-col items-center gap-2 text-center">
          <AuthLogo />
          <h1 className="font-semibold text-xl tracking-tight">{title}</h1>
          <p className="text-muted-foreground text-sm">{description}</p>
        </div>
        {children}
      </div>
    </div>
  );
}
