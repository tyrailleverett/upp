import type { ReactNode } from "react";
import { Toaster } from "@/components/ui/sonner";
import { useFlashToasts } from "@/hooks/use-flash-toasts";

export default function ToastLayout({ children }: { children: ReactNode }) {
  useFlashToasts();

  return (
    <>
      {children}
      <Toaster />
    </>
  );
}
