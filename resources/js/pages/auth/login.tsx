import { Head, Link, useForm, usePage } from "@inertiajs/react";
import { LockKeyholeIcon, MailIcon } from "lucide-react";
import type { FormEvent } from "react";
import { store } from "@/actions/App/Http/Controllers/Auth/LoginController";
import { PasswordInput } from "@/components/auth/password-input";
import { SocialButtons } from "@/components/auth/social-buttons";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from "@/components/ui/input-group";
import { Label } from "@/components/ui/label";
import AuthLayout from "@/layouts/auth-layout";
import { privacyPolicy, termsOfService } from "@/routes";

import type { SharedData } from "@/types";

export default function Login() {
  const { name } = usePage<SharedData>().props;
  const { data, setData, post, processing, errors } = useForm({
    email: "",
    password: "",
    remember: false,
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    post(store.url());
  }

  return (
    <>
      <Head title="Log in" />
      <AuthLayout
        description={
          <>
            Don&apos;t have an account?{" "}
            <Link className="underline underline-offset-4" href="/register">
              Sign up
            </Link>
          </>
        }
        title={`Welcome to ${name}`}
      >
        <SocialButtons />

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

          <Field data-invalid={!!errors.password}>
            <div className="flex items-center justify-between">
              <FieldLabel htmlFor="password">Password</FieldLabel>
              <Link
                className="text-muted-foreground text-sm underline underline-offset-4"
                href="/forgot-password"
              >
                Forgot your password?
              </Link>
            </div>
            <PasswordInput
              aria-invalid={!!errors.password}
              autoComplete="current-password"
              icon={LockKeyholeIcon}
              id="password"
              onChange={(e) => setData("password", e.target.value)}
              value={data.password}
            />
            <FieldError>{errors.password}</FieldError>
          </Field>

          <div className="flex items-center gap-2">
            <Checkbox
              checked={data.remember}
              id="remember"
              onCheckedChange={(checked) =>
                setData("remember", checked === true)
              }
            />
            <Label className="font-normal" htmlFor="remember">
              Remember me
            </Label>
          </div>

          <Button className="w-full" disabled={processing} type="submit">
            {processing ? "Logging in..." : "Log in"}
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
