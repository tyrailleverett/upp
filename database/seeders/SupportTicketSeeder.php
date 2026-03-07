<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SupportTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrFail();

        SupportTicket::factory()
            ->count(10)
            ->for($user)
            ->create();

        // Create some resolved tickets
        SupportTicket::factory()
            ->count(5)
            ->for($user)
            ->resolved()
            ->create();
    }
}
