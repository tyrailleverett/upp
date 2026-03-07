import { Head, router, useForm, usePage } from "@inertiajs/react";
import { type FormEvent, useState } from "react";
import { destroy as destroyAccount } from "@/actions/App/Http/Controllers/Settings/DeleteAccountController";
import {
  destroy,
  resend,
} from "@/actions/App/Http/Controllers/Settings/PendingEmailController";
import { update } from "@/actions/App/Http/Controllers/Settings/UpdateProfileController";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Field,
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import DashboardLayout from "@/layouts/dashboard/layout";
import SettingsLayout from "@/layouts/dashboard/settings-layout";
import type { SharedData } from "@/types";

type Props = {
  pendingEmail: string | null;
};

export default function Profile({ pendingEmail }: Props) {
  const { auth } = usePage<SharedData>().props;
  const [isResending, setIsResending] = useState(false);
  const [isCancelling, setIsCancelling] = useState(false);

  const {
    data,
    setData,
    put,
    processing,
    errors,
    recentlySuccessful,
    isDirty,
  } = useForm({
    name: auth.user.name,
    email: auth.user.email,
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    put(update.url());
  }

  function resendVerification() {
    setIsResending(true);
    router.post(
      resend.url(),
      {},
      {
        onFinish: () => setIsResending(false),
      }
    );
  }

  function cancelEmailChange() {
    setIsCancelling(true);
    router.delete(destroy.url(), {
      onFinish: () => setIsCancelling(false),
    });
  }

  return (
    <>
      <Head title="Profile Settings" />

      <div className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Profile Information</CardTitle>
            <CardDescription>
              Update your name and email address.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form className="space-y-6" onSubmit={submit}>
              <FieldSet>
                <FieldGroup>
                  <Field data-invalid={!!errors.name}>
                    <FieldLabel htmlFor="name">Name</FieldLabel>
                    <Input
                      aria-invalid={!!errors.name}
                      autoComplete="name"
                      id="name"
                      onChange={(e) => setData("name", e.target.value)}
                      value={data.name}
                    />
                    <FieldError>{errors.name}</FieldError>
                  </Field>

                  <Field data-invalid={!!errors.email}>
                    <FieldLabel htmlFor="email">Email</FieldLabel>
                    <Input
                      aria-invalid={!!errors.email}
                      autoComplete="email"
                      id="email"
                      onChange={(e) => setData("email", e.target.value)}
                      type="email"
                      value={data.email}
                    />
                    <FieldDescription>
                      {pendingEmail
                        ? `A verification email has been sent to ${pendingEmail}.`
                        : "If you change your email, you'll need to verify the new one."}
                    </FieldDescription>
                    <FieldError>{errors.email}</FieldError>
                  </Field>
                </FieldGroup>
              </FieldSet>

              <div className="flex items-center gap-3">
                <Button disabled={processing || !isDirty} type="submit">
                  {processing ? "Saving..." : "Save changes"}
                </Button>
                {recentlySuccessful && (
                  <p className="text-muted-foreground text-sm">Saved.</p>
                )}
              </div>
            </form>
          </CardContent>
        </Card>

        {pendingEmail && (
          <Card>
            <CardHeader>
              <CardTitle>Pending Email Verification</CardTitle>
              <CardDescription>
                We sent a verification link to{" "}
                <span className="font-medium">{pendingEmail}</span>. Please
                check your inbox.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex gap-3">
                <Button
                  disabled={isResending}
                  onClick={resendVerification}
                  variant="outline"
                >
                  {isResending ? "Resending..." : "Resend verification"}
                </Button>
                <Button
                  disabled={isCancelling}
                  onClick={cancelEmailChange}
                  variant="ghost"
                >
                  {isCancelling ? "Cancelling..." : "Cancel email change"}
                </Button>
              </div>
            </CardContent>
          </Card>
        )}

        <DeleteAccountCard />
      </div>
    </>
  );
}

function DeleteAccountCard() {
  const { auth } = usePage<SharedData>().props;
  const [open, setOpen] = useState(false);
  const {
    data,
    setData,
    delete: deleteAccount,
    processing,
    errors,
    reset,
  } = useForm({
    email: "",
    confirm: false,
  });

  function submit(e: FormEvent) {
    e.preventDefault();
    deleteAccount(destroyAccount.url(), {
      onSuccess: () => setOpen(false),
      preserveScroll: true,
    });
  }

  return (
    <Card className="border-destructive/50">
      <CardHeader>
        <CardTitle>Delete Account</CardTitle>
        <CardDescription>
          Permanently delete your account and all of its data. This action
          cannot be undone.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <AlertDialog
          onOpenChange={(isOpen) => {
            setOpen(isOpen);
            if (!isOpen) {
              reset();
            }
          }}
          open={open}
        >
          <AlertDialogTrigger asChild>
            <Button variant="destructive">Delete account</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <form onSubmit={submit}>
              <AlertDialogHeader>
                <AlertDialogTitle>
                  Are you sure you want to delete your account?
                </AlertDialogTitle>
                <AlertDialogDescription>
                  This action cannot be undone. All your data will be
                  permanently removed.
                </AlertDialogDescription>
              </AlertDialogHeader>

              <div className="my-4 space-y-4">
                <Field data-invalid={!!errors.email}>
                  <FieldLabel htmlFor="delete-email">
                    Type your email ({auth.user.email}) to confirm
                  </FieldLabel>
                  <Input
                    aria-invalid={!!errors.email}
                    id="delete-email"
                    onChange={(e) => setData("email", e.target.value)}
                    placeholder={auth.user.email}
                    type="email"
                    value={data.email}
                  />
                  <FieldError>{errors.email}</FieldError>
                </Field>

                <div className="space-y-1">
                  <div className="flex items-center gap-2">
                    <Checkbox
                      aria-invalid={!!errors.confirm}
                      checked={data.confirm}
                      id="delete-confirm"
                      onCheckedChange={(checked) =>
                        setData("confirm", checked === true)
                      }
                    />
                    <Label className="text-sm" htmlFor="delete-confirm">
                      I understand that my account and all of its data will be
                      permanently deleted
                    </Label>
                  </div>
                  {errors.confirm && (
                    <p className="text-destructive text-sm">{errors.confirm}</p>
                  )}
                </div>
              </div>

              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction
                  disabled={
                    processing ||
                    !data.confirm ||
                    data.email.toLowerCase() !== auth.user.email.toLowerCase()
                  }
                  onClick={(e) => {
                    e.preventDefault();
                    submit(e);
                  }}
                  variant="destructive"
                >
                  {processing ? "Deleting..." : "Delete account"}
                </AlertDialogAction>
              </AlertDialogFooter>
            </form>
          </AlertDialogContent>
        </AlertDialog>
      </CardContent>
    </Card>
  );
}

Profile.layout = (page: React.ReactNode) => (
  <DashboardLayout>
    <SettingsLayout>{page}</SettingsLayout>
  </DashboardLayout>
);
