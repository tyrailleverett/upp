import { Head, Link, useForm } from "@inertiajs/react";
import { KeyRoundIcon } from "lucide-react";
import { type FormEvent, useState } from "react";
import { Button } from "@/components/ui/button";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from "@/components/ui/input-group";
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/components/ui/input-otp";
import AuthLayout from "@/layouts/auth-layout";

export default function TwoFactorChallenge() {
  const [isRecovery, setIsRecovery] = useState(false);
  const { data, setData, post, processing, errors } = useForm({
    code: "",
    recovery_code: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post("/two-factor-challenge");
  }

  return (
    <>
      <Head title="Two-factor authentication" />
      <AuthLayout
        description={
          isRecovery
            ? "Enter one of your recovery codes to continue"
            : "Enter the code from your authenticator app"
        }
        title="Two-factor authentication"
      >
        <form className="space-y-4" onSubmit={submit}>
          {isRecovery ? (
            <Field data-invalid={!!errors.recovery_code}>
              <FieldLabel htmlFor="recovery_code">Recovery code</FieldLabel>
              <InputGroup>
                <InputGroupAddon>
                  <KeyRoundIcon />
                </InputGroupAddon>
                <InputGroupInput
                  aria-invalid={!!errors.recovery_code}
                  autoComplete="one-time-code"
                  id="recovery_code"
                  onChange={(e) => setData("recovery_code", e.target.value)}
                  type="text"
                  value={data.recovery_code}
                />
              </InputGroup>
              <FieldError>{errors.recovery_code}</FieldError>
            </Field>
          ) : (
            <Field data-invalid={!!errors.code}>
              <FieldLabel htmlFor="code">Authentication code</FieldLabel>
              <div className="flex justify-center">
                <InputOTP
                  aria-invalid={!!errors.code}
                  id="code"
                  maxLength={6}
                  onChange={(value) => setData("code", value)}
                  value={data.code}
                >
                  <InputOTPGroup>
                    <InputOTPSlot index={0} />
                    <InputOTPSlot index={1} />
                    <InputOTPSlot index={2} />
                    <InputOTPSlot index={3} />
                    <InputOTPSlot index={4} />
                    <InputOTPSlot index={5} />
                  </InputOTPGroup>
                </InputOTP>
              </div>
              <FieldError className="text-center">{errors.code}</FieldError>
            </Field>
          )}

          <Button className="w-full" disabled={processing} type="submit">
            {processing ? "Verifying..." : "Verify"}
          </Button>
        </form>

        <div className="mt-4 flex flex-col items-center gap-8 text-sm">
          <button
            className="text-muted-foreground underline underline-offset-4"
            onClick={() => setIsRecovery(!isRecovery)}
            type="button"
          >
            {isRecovery
              ? "Use authenticator code instead"
              : "Use a recovery code instead"}
          </button>
          <Link
            className="text-muted-foreground underline underline-offset-4"
            href="/login"
          >
            Log out
          </Link>
        </div>
      </AuthLayout>
    </>
  );
}
