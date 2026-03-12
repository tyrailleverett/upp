<?php

declare(strict_types=1);

namespace App\Mail\Preview;

use App\Contracts\Previewable;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class ResetPasswordPreview extends Mailable implements Previewable
{
    public function __construct(public User $user, public string $url, public int $expireMinutes)
    {
    }

    /**
     * Create a representative instance of this mailable for preview purposes.
     */
    public static function preview(): Mailable
    {
        return new self(
            user: User::factory()->make(),
            url: url('/reset-password?token=preview-token&email=preview@example.com'),
            expireMinutes: (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Reset Password Notification'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.reset-password',
            with: [
                'url' => $this->url,
                'user' => $this->user,
                'expireMinutes' => $this->expireMinutes,
            ],
        );
    }
}
