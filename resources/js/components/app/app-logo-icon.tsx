import { ZapIcon } from "lucide-react";
import { cn } from "@/lib/utils";

export default function AppLogoIcon({ className }: { className?: string }) {
  return <ZapIcon className={cn("size-5", className)} />;
}
