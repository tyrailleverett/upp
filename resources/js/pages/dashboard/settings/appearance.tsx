import { Head } from "@inertiajs/react";
import { MonitorIcon, MoonIcon, SunIcon } from "lucide-react";
import { useTheme } from "next-themes";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import DashboardLayout from "@/layouts/dashboard/layout";
import SettingsLayout from "@/layouts/dashboard/settings-layout";
import { cn } from "@/lib/utils";

const themes = [
  {
    value: "light",
    label: "Light",
    icon: SunIcon,
  },
  {
    value: "dark",
    label: "Dark",
    icon: MoonIcon,
  },
  {
    value: "system",
    label: "System",
    icon: MonitorIcon,
  },
] as const;

export default function Appearance() {
  const { theme, setTheme } = useTheme();

  return (
    <>
      <Head title="Appearance Settings" />

      <Card>
        <CardHeader>
          <CardTitle>Theme</CardTitle>
          <CardDescription>
            Select the theme for the application.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <RadioGroup
            className="gap-2"
            onValueChange={(value) => setTheme(value)}
            value={theme ?? "system"}
          >
            {themes.map((option) => (
              <Label
                className={cn(
                  "flex cursor-pointer items-center justify-between rounded-md border px-3 py-2 transition-colors",
                  (theme ?? "system") === option.value
                    ? "border-primary bg-primary/5"
                    : "border-border hover:border-primary/50"
                )}
                htmlFor={option.value}
                key={option.value}
              >
                <span className="flex items-center gap-2">
                  <option.icon className="size-4" />
                  <span className="font-medium text-sm">{option.label}</span>
                </span>
                <RadioGroupItem id={option.value} value={option.value} />
              </Label>
            ))}
          </RadioGroup>
        </CardContent>
      </Card>
    </>
  );
}

Appearance.layout = (page: React.ReactNode) => (
  <DashboardLayout>
    <SettingsLayout>{page}</SettingsLayout>
  </DashboardLayout>
);
