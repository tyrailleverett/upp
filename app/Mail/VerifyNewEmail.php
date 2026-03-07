<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Previewable;
use App\Models\User;
use Illuminate\Mail\Mailable;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail as BaseVerifyNewEmail;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

final class VerifyNewEmail extends BaseVerifyNewEmail implements Previewable
{
    /**
     * Create a representative instance of this mailable for preview purposes.
     */
    public static function preview(): Mailable
    {
        $pendingUserEmail = new PendingUserEmail([
            'email' => 'preview-new@example.com',
            'token' => 'preview-token',
        ]);

        $pendingUserEmail->setRelation('user', User::factory()->make());

        return new self($pendingUserEmail);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $this->subject(__('Verify New Email Address'));

        return $this->view('mail.verify-new-email', [
            'url' => $this->pendingUserEmail->verificationUrl(),
            'user' => $this->pendingUserEmail->user,
        ]);
    }
}
