import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";
import { redirect } from "@/actions/App/Http/Controllers/Auth/SocialiteController";
import SecurityController from "@/actions/App/Http/Controllers/Settings/SecurityController";
import { GoogleIcon } from "@/components/auth/social-buttons";
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
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import DashboardLayout from "@/layouts/dashboard/layout";
import SettingsLayout from "@/layouts/dashboard/settings-layout";

type ConnectedAccount = {
  id: number;
  provider: string;
  email: string;
  created_at: string;
};

type Provider = {
  value: string;
  label: string;
};

type ConnectedAccountsProps = {
  connectedAccounts: ConnectedAccount[];
  availableProviders: Provider[];
  canDisconnect: boolean;
};

const providerIcons: Record<string, React.ReactNode> = {
  google: GoogleIcon,
};

function DisconnectButton({
  account,
  canDisconnect,
}: {
  account: ConnectedAccount;
  canDisconnect: boolean;
}) {
  const [isDisconnecting, setIsDisconnecting] = useState(false);

  function handleDisconnect() {
    setIsDisconnecting(true);
    router.delete(`/settings/social-accounts/${account.id}`, {
      onFinish: () => setIsDisconnecting(false),
    });
  }

  return (
    <AlertDialog>
      <AlertDialogTrigger asChild>
        <Button size="sm" variant="outline">
          Disconnect
        </Button>
      </AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>
            {canDisconnect ? "Disconnect account?" : "Unable to disconnect"}
          </AlertDialogTitle>
          <AlertDialogDescription>
            {canDisconnect ? (
              "You will no longer be able to sign in with this account. You can reconnect it at any time."
            ) : (
              <>
                This is your only connected account and you don't have a
                password set. Disconnecting it would lock you out.{" "}
                <Link
                  className="text-foreground underline underline-offset-4 hover:no-underline"
                  href={SecurityController.url()}
                >
                  Set a password
                </Link>{" "}
                first, then you can safely disconnect this account.
              </>
            )}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          {canDisconnect ? (
            <>
              <AlertDialogCancel disabled={isDisconnecting}>
                Cancel
              </AlertDialogCancel>
              <AlertDialogAction
                disabled={isDisconnecting}
                onClick={handleDisconnect}
                variant="destructive"
              >
                {isDisconnecting ? "Disconnecting..." : "Disconnect"}
              </AlertDialogAction>
            </>
          ) : (
            <AlertDialogCancel>Close</AlertDialogCancel>
          )}
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
}

export default function ConnectedAccounts({
  connectedAccounts,
  availableProviders,
  canDisconnect,
}: ConnectedAccountsProps) {
  const connectedProviders = new Set(
    connectedAccounts.map((account) => account.provider)
  );

  return (
    <>
      <Head title="Connected Accounts" />
      <Card>
        <CardHeader>
          <CardTitle>Connected Accounts</CardTitle>
          <CardDescription>
            Manage the social accounts linked to your profile.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {availableProviders.map((provider) => {
              const account = connectedAccounts.find(
                (a) => a.provider === provider.value
              );
              const isConnected = connectedProviders.has(provider.value);

              return (
                <div
                  className="flex items-center justify-between rounded-lg border p-4"
                  key={provider.value}
                >
                  <div className="flex items-center gap-3">
                    <div className="flex size-10 items-center justify-center rounded-md border bg-background">
                      {providerIcons[provider.value]}
                    </div>
                    <div>
                      <div className="flex items-center gap-2">
                        <p className="font-medium text-sm">{provider.label}</p>
                        {isConnected ? (
                          <Badge
                            className="border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                            variant="outline"
                          >
                            Connected
                          </Badge>
                        ) : (
                          <Badge variant="outline">Not connected</Badge>
                        )}
                      </div>
                      {isConnected && account && (
                        <p className="text-muted-foreground text-sm">
                          {account.email}
                        </p>
                      )}
                    </div>
                  </div>

                  {isConnected && account ? (
                    <DisconnectButton
                      account={account}
                      canDisconnect={canDisconnect}
                    />
                  ) : (
                    <Button asChild size="sm" variant="outline">
                      <a href={redirect.url(provider.value)}>Connect</a>
                    </Button>
                  )}
                </div>
              );
            })}
          </div>
        </CardContent>
      </Card>
    </>
  );
}

ConnectedAccounts.layout = (page: React.ReactNode) => (
  <DashboardLayout>
    <SettingsLayout>{page}</SettingsLayout>
  </DashboardLayout>
);
