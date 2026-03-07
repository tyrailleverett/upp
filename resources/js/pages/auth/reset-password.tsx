import { Head, useForm } from "@inertiajs/react";
import { LockKeyholeIcon, MailIcon } from "lucide-react";
import type { FormEvent } from "react";
import { store } from "@/actions/App/Http/Controllers/Auth/ResetPasswordController";
import { PasswordInput } from "@/components/auth/password-input";
import { Button } from "@/components/ui/button";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from "@/components/ui/input-group";
import AuthLayout from "@/layouts/auth-layout";

interface ResetPasswordProps {
  token: string;
  email: string;
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
  const { data, setData, post, processing, errors } = useForm({
    token,
    email,
    password: "",
    password_confirmation: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url());
  }

  return (
    <>
      <Head title="Reset password" />
      <AuthLayout
        description="Enter your new password"
        title="Reset your password"
      >
        <form className="space-y-4" onSubmit={submit}>
          <input name="token" type="hidden" value={data.token} />

          <Field data-invalid={!!errors.email}>
            <FieldLabel htmlFor="email">Email</FieldLabel>
            <InputGroup>
              <InputGroupAddon>
                <MailIcon />
              </InputGroupAddon>
              <InputGroupInput
                aria-invalid={!!errors.email}
                autoComplete="email"
                id="email"
                onChange={(e) => setData("email", e.target.value)}
                type="email"
                value={data.email}
              />
            </InputGroup>
            <FieldError>{errors.email}</FieldError>
          </Field>

          <Field data-invalid={!!errors.password}>
            <FieldLabel htmlFor="password">New password</FieldLabel>
            <PasswordInput
              aria-invalid={!!errors.password}
              autoComplete="new-password"
              icon={LockKeyholeIcon}
              id="password"
              onChange={(e) => setData("password", e.target.value)}
              showStrength
              value={data.password}
            />
            <FieldError>{errors.password}</FieldError>
          </Field>

          <Field data-invalid={!!errors.password_confirmation}>
            <FieldLabel htmlFor="password_confirmation">
              Confirm password
            </FieldLabel>
            <PasswordInput
              aria-invalid={!!errors.password_confirmation}
              autoComplete="new-password"
              icon={LockKeyholeIcon}
              id="password_confirmation"
              onChange={(e) => setData("password_confirmation", e.target.value)}
              value={data.password_confirmation}
            />
            <FieldError>{errors.password_confirmation}</FieldError>
          </Field>

          <Button className="w-full" disabled={processing} type="submit">
            {processing ? "Resetting..." : "Reset password"}
          </Button>
        </form>
      </AuthLayout>
    </>
  );
}
