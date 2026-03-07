<?php

declare(strict_types=1);

use App\Mail\AccountDeletedMail;
use App\Mail\Preview\ResetPasswordPreview;
use App\Mail\SupportTicketReceivedMail;
use App\Mail\SupportTicketSubmittedMail;
use App\Mail\VerifyFirstEmail;
use App\Mail\VerifyNewEmail;

it('lists all previewable mailables on the index page', function (): void {
    $this->get(route('dev.mail.index'))
        ->assertSuccessful()
        ->assertSeeInOrder([
            'Mail Previews',
            'AccountDeletedMail',
            'ResetPasswordPreview',
            'SupportTicketReceivedMail',
            'SupportTicketSubmittedMail',
            'VerifyFirstEmail',
            'VerifyNewEmail',
        ]);
});

it('discovers all 6 previewable mailables', function (): void {
    $response = $this->get(route('dev.mail.index'));

    $response->assertSuccessful()
        ->assertSee('6 previewable templates');
});

it('renders the AccountDeletedMail preview', function (): void {
    $this->get(route('dev.mail.show', 'account-deleted-mail'))
        ->assertSuccessful()
        ->assertSee('Account Deleted')
        ->assertSee('sorry to see you go');
});

it('renders the VerifyFirstEmail preview', function (): void {
    $this->get(route('dev.mail.show', 'verify-first-email'))
        ->assertSuccessful()
        ->assertSee('Verify your email address');
});

it('renders the VerifyNewEmail preview', function (): void {
    $this->get(route('dev.mail.show', 'verify-new-email'))
        ->assertSuccessful()
        ->assertSee('Verify your new email address');
});

it('renders the ResetPasswordPreview preview', function (): void {
    $this->get(route('dev.mail.show', 'reset-password-preview'))
        ->assertSuccessful()
        ->assertSee('Reset your password');
});

it('renders the SupportTicketReceivedMail preview', function (): void {
    $this->get(route('dev.mail.show', 'support-ticket-received-mail'))
        ->assertSuccessful()
        ->assertSee('New Support Ticket Received');
});

it('renders the SupportTicketSubmittedMail preview', function (): void {
    $this->get(route('dev.mail.show', 'support-ticket-submitted-mail'))
        ->assertSuccessful()
        ->assertSee('Support Ticket Submitted');
});

it('returns 404 for an unknown mailable slug', function (): void {
    $this->get(route('dev.mail.show', 'nonexistent-mailable'))
        ->assertNotFound();
});

it('generates correct slugs from class names', function (string $slug, string $class): void {
    $this->get(route('dev.mail.show', $slug))
        ->assertSuccessful();
})->with([
    'AccountDeletedMail' => ['account-deleted-mail', AccountDeletedMail::class],
    'VerifyFirstEmail' => ['verify-first-email', VerifyFirstEmail::class],
    'VerifyNewEmail' => ['verify-new-email', VerifyNewEmail::class],
    'ResetPasswordPreview' => ['reset-password-preview', ResetPasswordPreview::class],
    'SupportTicketReceivedMail' => ['support-ticket-received-mail', SupportTicketReceivedMail::class],
    'SupportTicketSubmittedMail' => ['support-ticket-submitted-mail', SupportTicketSubmittedMail::class],
]);
