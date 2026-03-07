import { Check, X } from "lucide-react";
import { cn } from "@/lib/utils";

interface Requirement {
  label: string;
  isMet: boolean;
}

interface PasswordStrengthIndicatorProps {
  score: number;
  requirements: Requirement[];
  isFocused: boolean;
}

const strengthColors: Record<number, string> = {
  0: "bg-muted",
  1: "bg-red-500",
  2: "bg-orange-500",
  3: "bg-amber-500",
  4: "bg-yellow-500",
  5: "bg-emerald-500",
};

export function PasswordStrengthIndicator({
  score,
  requirements,
  isFocused,
}: PasswordStrengthIndicatorProps) {
  const widthPercent = (score / 5) * 100;

  return (
    <div className="space-y-2">
      <meter
        aria-label="Password strength"
        className="sr-only"
        max={5}
        min={0}
        value={score}
      />
      <div
        aria-hidden="true"
        className="h-1.5 w-full overflow-hidden rounded-full bg-muted"
      >
        <div
          className={cn(
            "h-full rounded-full transition-all duration-500",
            strengthColors[score]
          )}
          style={{ width: `${widthPercent}%` }}
        />
      </div>

      <div
        className={cn(
          "grid transition-all duration-300 ease-in-out",
          isFocused
            ? "grid-rows-[1fr] opacity-100"
            : "grid-rows-[0fr] opacity-0"
        )}
      >
        <ul className="space-y-1 overflow-hidden">
          {requirements.map((req) => (
            <li
              className={cn(
                "flex items-center gap-1.5 text-xs transition-colors duration-200",
                req.isMet ? "text-emerald-600" : "text-muted-foreground"
              )}
              key={req.label}
            >
              {req.isMet ? (
                <Check className="size-3" />
              ) : (
                <X className="size-3" />
              )}
              {req.label}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
