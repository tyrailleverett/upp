import { Head, router, useForm, usePage } from "@inertiajs/react";
import { TriangleAlertIcon } from "lucide-react";
import {
  type FormEvent,
  useCallback,
  useEffect,
  useRef,
  useState,
} from "react";
import { toast } from "sonner";
import { update as updatePassword } from "@/actions/App/Http/Controllers/Settings/ChangePasswordController";
import { PasswordInput } from "@/components/auth/password-input";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import {
  Field,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSet,
} from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/components/ui/input-otp";
import DashboardLayout from "@/layouts/dashboard/layout";
import SettingsLayout from "@/layouts/dashboard/settings-layout";
import type { SharedData } from "@/types";

type TwoFactorState = "disabled" | "enabling" | "confirming" | "enabled";

export default function Security() {
  const { auth } = usePage<SharedData>().props;

  return (
    <>
      <Head title="Security Settings" />
      <div className="space-y-6">
        {auth.has_password && <ChangePasswordCard />}
        <TwoFactorCard />
      </div>
    </>
  );
}

function ChangePasswordCard() {
  const { data, setData, put, processing, errors, reset, recentlySuccessful } =
    useForm({
      current_password: "",
      password: "",
      password_confirmation: "",
    });

  function submit(e: FormEvent) {
    e.preventDefault();
    put(updatePassword.url(), {
      onSuccess: () => reset(),
    });
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Change Password</CardTitle>
        <CardDescription>
          Ensure your account is using a long, random password to stay secure.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form className="space-y-6" onSubmit={submit}>
          <FieldSet>
            <FieldGroup>
              <Field data-invalid={!!errors.current_password}>
                <FieldLabel htmlFor="current_password">
                  Current password
                </FieldLabel>
                <PasswordInput
                  aria-invalid={!!errors.current_password}
                  autoComplete="current-password"
                  id="current_password"
                  onChange={(e) => setData("current_password", e.target.value)}
                  value={data.current_password}
                />
                <FieldError>{errors.current_password}</FieldError>
              </Field>

              <Field data-invalid={!!errors.password}>
                <FieldLabel htmlFor="password">New password</FieldLabel>
                <PasswordInput
                  aria-invalid={!!errors.password}
                  autoComplete="new-password"
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
                  id="password_confirmation"
                  onChange={(e) =>
                    setData("password_confirmation", e.target.value)
                  }
                  value={data.password_confirmation}
                />
                <FieldError>{errors.password_confirmation}</FieldError>
              </Field>
            </FieldGroup>
          </FieldSet>

          <div className="flex items-center gap-3">
            <Button disabled={processing} type="submit">
              {processing ? "Updating..." : "Update password"}
            </Button>
            {recentlySuccessful && (
              <p className="text-muted-foreground text-sm">Saved.</p>
            )}
          </div>
        </form>
      </CardContent>
    </Card>
  );
}

function QrCodeDisplay({ svg }: { svg: string }) {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (ref.current) {
      ref.current.innerHTML = svg;
    }
  }, [svg]);

  return (
    <div className="inline-block rounded-lg border bg-white p-4" ref={ref} />
  );
}

function SetupKeyDisplay({ setupKey }: { setupKey: string }) {
  const [copied, setCopied] = useState(false);

  function copyToClipboard() {
    navigator.clipboard.writeText(setupKey);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  }

  return (
    <Collapsible>
      <CollapsibleTrigger className="inline-flex cursor-pointer items-center gap-1.5 text-muted-foreground text-sm underline underline-offset-4 transition-colors hover:text-foreground">
        Can't scan? Reveal setup key
      </CollapsibleTrigger>
      <CollapsibleContent>
        <div className="mt-3 flex items-center gap-2">
          <Input
            className="font-mono tracking-wider"
            onClick={(e) => e.currentTarget.select()}
            readOnly
            value={setupKey}
          />
          <Button
            onClick={copyToClipboard}
            size="sm"
            type="button"
            variant="outline"
          >
            {copied ? "Copied!" : "Copy"}
          </Button>
        </div>
      </CollapsibleContent>
    </Collapsible>
  );
}

function RecoveryCodesModal({
  codes,
  onClose,
  open,
}: {
  codes: string[];
  onClose: () => void;
  open: boolean;
}) {
  const [copied, setCopied] = useState(false);

  async function copyToClipboard() {
    try {
      await navigator.clipboard.writeText(codes.join("\n"));
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch {
      toast.error("Failed to copy to clipboard.");
    }
  }

  return (
    <AlertDialog open={open}>
      <AlertDialogContent onEscapeKeyDown={(e) => e.preventDefault()}>
        <AlertDialogHeader>
          <AlertDialogTitle>Recovery Codes</AlertDialogTitle>
          <AlertDialogDescription>
            Store these recovery codes in a secure password manager. They can be
            used to recover access to your account if you lose your two-factor
            authentication device.
          </AlertDialogDescription>
        </AlertDialogHeader>

        {codes.length > 0 && (
          <div className="rounded-lg border bg-muted p-4 font-mono text-sm">
            {codes.map((code) => (
              <div key={code}>{code}</div>
            ))}
          </div>
        )}

        <div className="flex items-start gap-2 rounded-md bg-amber-50 p-3 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400">
          <TriangleAlertIcon className="mt-0.5 size-4 shrink-0" />
          <p className="text-sm">
            These codes will not be shown again. Please copy or save them before
            closing this dialog.
          </p>
        </div>

        <AlertDialogFooter>
          <Button onClick={copyToClipboard} variant="outline">
            {copied ? "Copied!" : "Copy codes"}
          </Button>
          <AlertDialogAction onClick={onClose}>Done</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}

function TwoFactorCard() {
  const { auth } = usePage<SharedData>().props;
  const [state, setState] = useState<TwoFactorState>(
    auth.two_factor_enabled ? "enabled" : "disabled"
  );
  const [qrCode, setQrCode] = useState("");
  const [setupKey, setSetupKey] = useState("");
  const [recoveryCodes, setRecoveryCodes] = useState<string[]>([]);
  const [confirmCode, setConfirmCode] = useState("");
  const [isProcessing, setIsProcessing] = useState(false);
  const [error, setError] = useState("");
  const [showCodesModal, setShowCodesModal] = useState(false);
  const [showDisableConfirm, setShowDisableConfirm] = useState(false);

  const fetchQrCode = useCallback(async () => {
    try {
      const response = await fetch("/user/two-factor-qr-code", {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });
      if (!response.ok) {
        throw new Error("Failed to load QR code");
      }
      const data = (await response.json()) as { svg: string };
      setQrCode(data.svg);
    } catch {
      setError("Unable to load QR code. Please try again.");
    }
  }, []);

  const fetchSetupKey = useCallback(async () => {
    try {
      const response = await fetch("/user/two-factor-secret-key", {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });
      if (!response.ok) {
        throw new Error("Failed to load setup key");
      }
      const data = (await response.json()) as { secretKey: string };
      setSetupKey(data.secretKey);
    } catch {
      setError("Unable to load setup key. Please try again.");
    }
  }, []);

  const fetchRecoveryCodes = useCallback(async (showModal = false) => {
    try {
      const response = await fetch("/user/two-factor-recovery-codes", {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });
      if (!response.ok) {
        throw new Error("Failed to load recovery codes");
      }
      const data = (await response.json()) as string[];
      setRecoveryCodes(data);
      if (showModal) {
        setShowCodesModal(true);
      }
    } catch {
      setError("Unable to load recovery codes. Please try again.");
    }
  }, []);

  function enable() {
    setIsProcessing(true);
    setError("");
    router.post(
      "/user/two-factor-authentication",
      {},
      {
        onSuccess: () => {
          fetchQrCode();
          fetchSetupKey();
          setState("confirming");
        },
        onFinish: () => setIsProcessing(false),
      }
    );
  }

  function confirm() {
    setIsProcessing(true);
    setError("");
    router.post(
      "/user/confirmed-two-factor-authentication",
      { code: confirmCode },
      {
        onSuccess: () => {
          setState("enabled");
          setConfirmCode("");
          fetchRecoveryCodes(true);
        },
        onError: () => {
          setError("Invalid authentication code. Please try again.");
          setConfirmCode("");
        },
        onFinish: () => setIsProcessing(false),
      }
    );
  }

  function regenerateRecoveryCodes() {
    setIsProcessing(true);
    setError("");
    router.post(
      "/user/two-factor-recovery-codes",
      {},
      {
        onSuccess: () => fetchRecoveryCodes(true),
        onError: () =>
          setError("Unable to regenerate recovery codes. Please try again."),
        onFinish: () => setIsProcessing(false),
      }
    );
  }

  function disable() {
    setIsProcessing(true);
    router.delete("/user/two-factor-authentication", {
      onSuccess: () => {
        setState("disabled");
        setShowDisableConfirm(false);
        setQrCode("");
        setSetupKey("");
        setRecoveryCodes([]);
      },
      onFinish: () => setIsProcessing(false),
    });
  }

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center gap-3">
          <CardTitle>Two-Factor Authentication</CardTitle>
          <Badge variant={state === "enabled" ? "default" : "secondary"}>
            {state === "enabled" ? "Enabled" : "Disabled"}
          </Badge>
        </div>
        <CardDescription>
          Add additional security to your account using two-factor
          authentication.
        </CardDescription>
      </CardHeader>
      <CardContent>
        {state === "disabled" && (
          <Button disabled={isProcessing} onClick={enable}>
            {isProcessing ? "Enabling..." : "Enable 2FA"}
          </Button>
        )}

        {state === "confirming" && (
          <div className="space-y-6">
            {qrCode && (
              <div className="space-y-4">
                <p className="text-sm">
                  Scan this QR code with your authenticator app, then enter the
                  code below to confirm.
                </p>
                <QrCodeDisplay svg={qrCode} />
                {setupKey && <SetupKeyDisplay setupKey={setupKey} />}
              </div>
            )}

            <Field data-invalid={!!error}>
              <FieldLabel htmlFor="otp">Authentication code</FieldLabel>
              <div className="flex justify-start">
                <InputOTP
                  id="otp"
                  maxLength={6}
                  onChange={(value) => setConfirmCode(value)}
                  value={confirmCode}
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
              <FieldError>{error}</FieldError>
            </Field>

            <div className="flex gap-3">
              <Button
                disabled={isProcessing || confirmCode.length < 6}
                onClick={confirm}
              >
                {isProcessing ? "Confirming..." : "Confirm"}
              </Button>
              <Button disabled={isProcessing} onClick={disable} variant="ghost">
                Cancel
              </Button>
            </div>
          </div>
        )}

        {state === "enabled" && (
          <div className="space-y-6">
            <p className="text-muted-foreground text-sm">
              Two-factor authentication is enabled for your account.
            </p>

            {error && <p className="text-destructive text-sm">{error}</p>}

            <div className="flex gap-3">
              <Button
                disabled={isProcessing}
                onClick={regenerateRecoveryCodes}
                variant="outline"
              >
                {isProcessing ? "Regenerating..." : "Regenerate recovery codes"}
              </Button>
              <Button
                disabled={isProcessing}
                onClick={() => setShowDisableConfirm(true)}
                variant="destructive"
              >
                Disable 2FA
              </Button>
            </div>
          </div>
        )}
      </CardContent>

      <RecoveryCodesModal
        codes={recoveryCodes}
        onClose={() => setShowCodesModal(false)}
        open={showCodesModal}
      />

      <AlertDialog open={showDisableConfirm}>
        <AlertDialogContent onEscapeKeyDown={(e) => e.preventDefault()}>
          <AlertDialogHeader>
            <AlertDialogTitle>
              Disable Two-Factor Authentication
            </AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to disable two-factor authentication? This
              will remove the additional security layer from your account.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <Button
              disabled={isProcessing}
              onClick={() => setShowDisableConfirm(false)}
              variant="outline"
            >
              Cancel
            </Button>
            <Button
              disabled={isProcessing}
              onClick={disable}
              variant="destructive"
            >
              {isProcessing ? "Disabling..." : "Disable 2FA"}
            </Button>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Card>
  );
}

Security.layout = (page: React.ReactNode) => (
  <DashboardLayout>
    <SettingsLayout>{page}</SettingsLayout>
  </DashboardLayout>
);
