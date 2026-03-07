import { Head, Link, useForm } from "@inertiajs/react";
import { LockKeyholeIcon, MailIcon, UserIcon } from "lucide-react";
import type { FormEvent } from "react";
import { store } from "@/actions/App/Http/Controllers/Auth/RegisterController";
import { PasswordInput } from "@/components/auth/password-input";
import { SocialButtons } from "@/components/auth/social-buttons";
import { Button } from "@/components/ui/button";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from "@/components/ui/input-group";
import AuthLayout from "@/layouts/auth-layout";
import { privacyPolicy, termsOfService } from "@/routes";

export default function Register() {
  const { data, setData, post, processing, errors } = useForm({
    name: "",
    email: "",
    password: "",
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url());
  }

  return (
    <>
      <Head title="Create an account" />
      <AuthLayout
        description={
          <>
            Already have an account?{" "}
            <Link className="underline underline-offset-4" href="/login">
              Log in
            </Link>
          </>
        }
        title="Create an account"
      >
        <SocialButtons />

        <form className="space-y-4" onSubmit={submit}>
          <Field data-invalid={!!errors.name}>
            <FieldLabel htmlFor="name">Name</FieldLabel>
            <InputGroup>
              <InputGroupAddon>
                <UserIcon />
              </InputGroupAddon>
              <InputGroupInput
                aria-invalid={!!errors.name}
                autoComplete="name"
                id="name"
                onChange={(e) => setData("name", e.target.value)}
                placeholder="Your name"
                type="text"
                value={data.name}
              />
            </InputGroup>
            <FieldError>{errors.name}</FieldError>
          </Field>

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

          <Field data-invalid={!!errors.password}>
            <FieldLabel htmlFor="password">Password</FieldLabel>
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

          <Button className="w-full" disabled={processing} type="submit">
            {processing ? "Creating account..." : "Register"}
          </Button>
        </form>

        <p className="mt-6 text-center text-muted-foreground text-xs">
          By continuing, you agree to our
          <br />
          <Link
            className="underline underline-offset-4"
            href={termsOfService.url()}
          >
            Terms of Service
          </Link>{" "}
          and{" "}
          <Link
            className="underline underline-offset-4"
            href={privacyPolicy.url()}
          >
            Privacy Policy
          </Link>
          .
        </p>
      </AuthLayout>
    </>
  );
}
