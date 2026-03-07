import { useMemo } from "react";

const HAS_UPPERCASE = /[A-Z]/;
const HAS_LOWERCASE = /[a-z]/;
const HAS_NUMBER = /\d/;
const HAS_SPECIAL = /[^A-Za-z0-9]/;

interface Requirement {
  label: string;
  isMet: boolean;
}

interface PasswordStrength {
  requirements: Requirement[];
  score: number;
}

export function usePasswordStrength(password: string): PasswordStrength {
  return useMemo(() => {
    const requirements: Requirement[] = [
      { label: "At least 8 characters", isMet: password.length >= 8 },
      { label: "One uppercase letter", isMet: HAS_UPPERCASE.test(password) },
      { label: "One lowercase letter", isMet: HAS_LOWERCASE.test(password) },
      { label: "One number", isMet: HAS_NUMBER.test(password) },
      {
        label: "One special character",
        isMet: HAS_SPECIAL.test(password),
      },
    ];

    const score = requirements.filter((r) => r.isMet).length;

    return { requirements, score };
  }, [password]);
}
