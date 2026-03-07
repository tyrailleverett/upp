<?php

declare(strict_types=1);

use App\Mail\VerifyFirstEmail;
use App\Mail\VerifyNewEmail;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

it('uses the custom verify email template via VerifyEmail notification', function (): void {
    $user = User::factory()->unverified()->create();

    $notification = new VerifyEmail;
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Verify Email Address')
        ->and($mail->view)->toBe('mail.verify-email')
        ->and($mail->viewData['user'])->toBe($user)
        ->and($mail->viewData['url'])->toBeString();
});

it('uses the custom reset password template via ResetPassword notification', function (): void {
    $user = User::factory()->create();

    $notification = new ResetPassword('test-token');
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Reset Password Notification')
        ->and($mail->view)->toBe('mail.reset-password')
        ->and($mail->viewData['user'])->toBe($user)
        ->and($mail->viewData['url'])->toContain('reset-password')
        ->and($mail->viewData['url'])->toContain('test-token')
        ->and($mail->viewData['expireMinutes'])->toBeInt();
});

it('configures verify-new-email package to use custom mailable classes', function (): void {
    expect(config('verify-new-email.mailable_for_first_verification'))
        ->toBe(VerifyFirstEmail::class)
        ->and(config('verify-new-email.mailable_for_new_email'))
        ->toBe(VerifyNewEmail::class);
});

it('renders the verify email template without errors', function (): void {
    $user = User::factory()->make();

    $html = view('mail.verify-email', [
        'url' => 'https://example.com/verify',
        'user' => $user,
    ])->render();

    expect($html)
        ->toContain('Verify your email address')
        ->toContain(e($user->name))
        ->toContain('https://example.com/verify')
        ->toContain('Verify Email Address');
});

it('renders the reset password template without errors', function (): void {
    $user = User::factory()->make();

    $html = view('mail.reset-password', [
        'url' => 'https://example.com/reset',
        'user' => $user,
        'expireMinutes' => 60,
    ])->render();

    expect($html)
        ->toContain('Reset your password')
        ->toContain(e($user->name))
        ->toContain('https://example.com/reset')
        ->toContain('60 minutes');
});

it('renders the verify new email template without errors', function (): void {
    $user = User::factory()->make();

    $html = view('mail.verify-new-email', [
        'url' => 'https://example.com/verify-new',
        'user' => $user,
    ])->render();

    expect($html)
        ->toContain('Verify your new email address')
        ->toContain(e($user->name))
        ->toContain('https://example.com/verify-new')
        ->toContain('Verify New Email Address');
});
