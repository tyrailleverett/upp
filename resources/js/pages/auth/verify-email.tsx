import { Head, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import { resend } from "@/actions/App/Http/Controllers/Auth/EmailVerificationController";
import LogoutController from "@/actions/App/Http/Controllers/Auth/LogoutController";
import { Button } from "@/components/ui/button";
import AuthLayout from "@/layouts/auth-layout";

export default function VerifyEmail() {
  const resendForm = useForm({});
  const logoutForm = useForm({});

  function submit(e: FormEvent) {
    e.preventDefault();
    resendForm.post(resend.url());
  }

  function logout(e: FormEvent) {
    e.preventDefault();
    logoutForm.post(LogoutController.url());
  }

  return (
    <>
      <Head title="Verify email" />
      <AuthLayout
        description="We sent a verification link to your email address. Please check your inbox."
        title="Verify your email"
      >
        <form className="space-y-4" onSubmit={submit}>
          <Button
            className="w-full"
            disabled={resendForm.processing}
            type="submit"
          >
            {resendForm.processing ? "Sending..." : "Resend verification email"}
          </Button>
        </form>

        <form className="mt-4 text-center text-sm" onSubmit={logout}>
          <button
            className="text-muted-foreground underline underline-offset-4"
            disabled={logoutForm.processing}
            type="submit"
          >
            Log out
          </button>
        </form>
      </AuthLayout>
    </>
  );
}
