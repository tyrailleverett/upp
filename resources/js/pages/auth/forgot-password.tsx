import { Head, Link, useForm } from "@inertiajs/react";
import { MailIcon } from "lucide-react";
import type { FormEvent } from "react";
import { store } from "@/actions/App/Http/Controllers/Auth/ForgotPasswordController";
import { Button } from "@/components/ui/button";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from "@/components/ui/input-group";
import AuthLayout from "@/layouts/auth-layout";

export default function ForgotPassword() {
  const { data, setData, post, processing, errors } = useForm({
    email: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url());
  }

  return (
    <>
      <Head title="Forgot password" />
      <AuthLayout
        description="Enter your email and we'll send a reset link"
        title="Forgot your password?"
      >
        <form className="space-y-4" onSubmit={submit}>
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
                placeholder="email@example.com"
                type="email"
                value={data.email}
              />
            </InputGroup>
            <FieldError>{errors.email}</FieldError>
          </Field>

          <Button className="w-full" disabled={processing} type="submit">
            {processing ? "Sending..." : "Send reset link"}
          </Button>
        </form>

        <p className="mt-4 text-center text-sm">
          <Link
            className="text-muted-foreground underline underline-offset-4"
            href="/login"
          >
            Back to log in
          </Link>
        </p>
      </AuthLayout>
    </>
  );
}
