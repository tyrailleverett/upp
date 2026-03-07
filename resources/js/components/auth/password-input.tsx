import type { LucideIcon } from "lucide-react";
import { Eye, EyeOff } from "lucide-react";
import {
  type ChangeEvent,
  type FocusEvent,
  useCallback,
  useEffect,
  useRef,
  useState,
} from "react";
import { PasswordStrengthIndicator } from "@/components/auth/password-strength-indicator";
import {
  InputGroup,
  InputGroupAddon,
  InputGroupButton,
  InputGroupInput,
} from "@/components/ui/input-group";
import { usePasswordStrength } from "@/hooks/use-password-strength";
import { cn } from "@/lib/utils";

interface PasswordInputProps {
  id: string;
  value: string;
  onChange: (e: ChangeEvent<HTMLInputElement>) => void;
  autoComplete?: string;
  "aria-invalid"?: boolean;
  icon?: LucideIcon;
  showStrength?: boolean;
  className?: string;
}

export function PasswordInput({
  id,
  value,
  onChange,
  autoComplete,
  "aria-invalid": ariaInvalid,
  icon: Icon,
  showStrength = false,
  className,
}: PasswordInputProps) {
  const [isVisible, setIsVisible] = useState(false);
  const [isFocused, setIsFocused] = useState(false);
  const groupRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const { requirements, score } = usePasswordStrength(value);

  useEffect(() => {
    if (!inputRef.current) {
      return;
    }

    if (!showStrength) {
      inputRef.current.setCustomValidity("");
      return;
    }

    const allMet = requirements.every((r) => r.isMet);
    inputRef.current.setCustomValidity(
      value.length > 0 && !allMet
        ? "Please meet all password requirements."
        : ""
    );
  }, [showStrength, requirements, value]);

  const handleBlur = useCallback((e: FocusEvent) => {
    const relatedTarget = e.relatedTarget as Node | null;
    if (groupRef.current?.contains(relatedTarget)) {
      return;
    }
    setIsFocused(false);
  }, []);

  return (
    <div className={cn("space-y-2", className)}>
      <InputGroup onBlur={handleBlur} ref={groupRef}>
        {Icon && (
          <InputGroupAddon align="inline-start">
            <Icon className="size-4" />
          </InputGroupAddon>
        )}
        <InputGroupInput
          aria-invalid={ariaInvalid}
          autoComplete={autoComplete}
          id={id}
          onChange={onChange}
          onFocus={() => setIsFocused(true)}
          ref={inputRef}
          type={isVisible ? "text" : "password"}
          value={value}
        />
        <InputGroupAddon align="inline-end">
          <InputGroupButton
            aria-label={isVisible ? "Hide password" : "Show password"}
            onClick={() => setIsVisible((prev) => !prev)}
            size="icon-xs"
          >
            {isVisible ? (
              <EyeOff className="size-4" />
            ) : (
              <Eye className="size-4" />
            )}
          </InputGroupButton>
        </InputGroupAddon>
      </InputGroup>

      {showStrength && (
        <div
          className={cn(
            "grid transition-all duration-300 ease-in-out",
            isFocused || value.length > 0
              ? "grid-rows-[1fr] opacity-100"
              : "grid-rows-[0fr] opacity-0"
          )}
        >
          <div className="overflow-hidden">
            <PasswordStrengthIndicator
              isFocused={isFocused}
              requirements={requirements}
              score={score}
            />
          </div>
        </div>
      )}
    </div>
  );
}
