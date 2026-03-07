<?php

declare(strict_types=1);

namespace App\Actions\Support;

use App\Mail\SupportTicketReceivedMail;
use App\Mail\SupportTicketSubmittedMail;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

final class CreateSupportTicketAction
{
    /**
     * @param  array{title: string, description: string, topic: string}  $data
     */
    public function execute(User $user, array $data): SupportTicket
    {
        $ticket = $user->supportTickets()->create($data);

        Cache::increment("user:{$user->id}:support-tickets:version");
        Cache::forget("user:{$user->id}:support-tickets");

        Mail::to($user->email)->send(new SupportTicketSubmittedMail($ticket));

        $adminEmail = config('support.admin_email');

        if ($adminEmail !== null && $adminEmail !== '') {
            Mail::to($adminEmail)->send(new SupportTicketReceivedMail($ticket));
        }

        return $ticket;
    }
}
