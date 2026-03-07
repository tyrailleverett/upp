import { Head, Link, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import { store } from "@/actions/App/Http/Controllers/Feedback/DeletionFeedbackController";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Textarea } from "@/components/ui/textarea";

const DELETION_REASONS = [
  { value: "too_expensive", label: "Too expensive" },
  { value: "not_useful", label: "Not useful for my needs" },
  { value: "found_alternative", label: "Found an alternative" },
  { value: "privacy_concerns", label: "Privacy concerns" },
  { value: "too_complex", label: "Too complex to use" },
  { value: "missing_features", label: "Missing features I need" },
  { value: "other", label: "Other" },
] as const;

interface AccountDeletionProps {
  email: string;
}

export default function AccountDeletion({ email }: AccountDeletionProps) {
  const { data, setData, post, processing, errors } = useForm({
    email,
    reason: "",
    comment: "",
  });

  function handleSubmit(event: FormEvent) {
    event.preventDefault();
    post(store.url());
  }

  return (
    <>
      <Head title="Account Deletion Feedback" />
      <div className="flex min-h-screen items-center justify-center bg-background p-4">
        <Card className="w-full max-w-lg">
          <CardHeader>
            <CardTitle>We'd love your feedback</CardTitle>
            <CardDescription>
              Your account has been deleted. Before you go, would you mind
              telling us why you left? This helps us improve.
            </CardDescription>
          </CardHeader>
          <form onSubmit={handleSubmit}>
            <CardContent className="space-y-6">
              <input name="email" type="hidden" value={data.email} />

              <div className="space-y-3">
                <Label>Why did you delete your account?</Label>
                <RadioGroup
                  onValueChange={(value) => setData("reason", value)}
                  value={data.reason}
                >
                  {DELETION_REASONS.map((reason) => (
                    <div className="flex items-center gap-2" key={reason.value}>
                      <RadioGroupItem id={reason.value} value={reason.value} />
                      <Label className="font-normal" htmlFor={reason.value}>
                        {reason.label}
                      </Label>
                    </div>
                  ))}
                </RadioGroup>
                {errors.reason && (
                  <p className="text-destructive text-sm">{errors.reason}</p>
                )}
              </div>

              <div className="space-y-2">
                <Label htmlFor="comment">
                  Anything else you'd like to share?{" "}
                  <span className="text-muted-foreground">(optional)</span>
                </Label>
                <Textarea
                  id="comment"
                  onChange={(event) => setData("comment", event.target.value)}
                  placeholder="Tell us more about your experience..."
                  rows={4}
                  value={data.comment}
                />
                {errors.comment && (
                  <p className="text-destructive text-sm">{errors.comment}</p>
                )}
              </div>
            </CardContent>
            <CardFooter className="justify-between">
              <Button asChild variant="ghost">
                <Link href="/">Skip</Link>
              </Button>
              <Button disabled={processing || !data.reason} type="submit">
                {processing ? "Sending..." : "Send Feedback"}
              </Button>
            </CardFooter>
          </form>
        </Card>
      </div>
    </>
  );
}
