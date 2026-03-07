<?php

declare(strict_types=1);

use App\Mail\AccountDeletedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('sends a farewell email when a user is soft deleted', function (): void {
    Mail::fake();

    $user = User::factory()->create();

    $user->delete();

    Mail::assertQueued(AccountDeletedMail::class, function (AccountDeletedMail $mail) use ($user): bool {
        return $mail->hasTo($user->email) && $mail->user->is($user);
    });
});

it('does not send a farewell email when a user is force deleted', function (): void {
    Mail::fake();

    $user = User::factory()->create();

    $user->forceDelete();

    Mail::assertNotQueued(AccountDeletedMail::class);
});

it('renders the account deleted email template without errors', function (): void {
    $user = User::factory()->make();

    $html = view('mail.account-deleted', [
        'user' => $user,
        'feedbackUrl' => 'https://example.com/feedback/account-deletion?signature=test',
    ])->render();

    expect($html)
        ->toContain('sorry to see you go')
        ->toContain($user->name)
        ->toContain(config('app.name'));
});
