<?php

declare(strict_types=1);

namespace App\Observers;

use App\Mail\AccountDeletedMail;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Mail;

final class UserObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "trashed" event.
     */
    public function trashed(User $user): void
    {
        Mail::to($user->email)->send(new AccountDeletedMail($user));
    }
}
