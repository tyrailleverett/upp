<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Previewable;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

final class AccountDeletedMail extends Mailable implements Previewable, ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    /**
     * Create a representative instance of this mailable for preview purposes.
     */
    public static function preview(): Mailable
    {
        return new self(
            user: User::factory()->make(),
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Account Deleted'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.account-deleted',
            with: [
                'feedbackUrl' => URL::temporarySignedRoute(
                    'feedback.account-deletion.create',
                    now()->addDays(7),
                    ['email' => $this->user->email],
                ),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
