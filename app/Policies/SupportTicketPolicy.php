<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

final class SupportTicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SupportTicket $supportTicket): bool
    {
        return $user->id === $supportTicket->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SupportTicket $supportTicket): bool
    {
        return $user->id === $supportTicket->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SupportTicket $supportTicket): bool
    {
        return $user->id === $supportTicket->user_id;
    }
}
