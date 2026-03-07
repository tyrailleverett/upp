<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => config('support.admin_email'),
        ]);

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => config('auth.defaults.guard'),
        ]);
        $user->assignRole($superAdminRole);

        $this->call([
            SubscriptionSeeder::class,
            SupportTicketSeeder::class,
        ]);
    }
}
