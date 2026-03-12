<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\ComponentStatusLog;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (! $user instanceof User) {
            $user = User::factory()->create([
                'name' => 'Site Test User',
                'email' => 'sitetest@example.com',
            ]);
        }

        // Published site with 5 components across 2 groups
        $publishedSite = Site::factory()->published()->create([
            'user_id' => $user->id,
            'name' => 'Acme Status',
            'slug' => 'acme-status',
            'description' => 'Public status page for Acme services.',
        ]);

        $components = [
            ['name' => 'API', 'group' => 'Core Services', 'status' => ComponentStatus::Operational, 'sort_order' => 0],
            ['name' => 'Database', 'group' => 'Core Services', 'status' => ComponentStatus::Operational, 'sort_order' => 1],
            ['name' => 'Frontend', 'group' => 'Core Services', 'status' => ComponentStatus::DegradedPerformance, 'sort_order' => 2],
            ['name' => 'CDN', 'group' => 'Infrastructure', 'status' => ComponentStatus::Operational, 'sort_order' => 3],
            ['name' => 'Worker', 'group' => 'Infrastructure', 'status' => ComponentStatus::Operational, 'sort_order' => 4],
        ];

        foreach ($components as $componentData) {
            $componentFactory = Component::factory()->inGroup($componentData['group']);

            if ($componentData['status'] === ComponentStatus::DegradedPerformance) {
                $componentFactory = $componentFactory->degraded();
            }

            $component = $componentFactory->create([
                'site_id' => $publishedSite->id,
                'name' => $componentData['name'],
                'sort_order' => $componentData['sort_order'],
            ]);

            // Seed 3 historical status log entries spanning the last 3 days
            for ($day = 3; $day >= 1; $day--) {
                ComponentStatusLog::factory()->create([
                    'component_id' => $component->id,
                    'status' => ComponentStatus::Operational,
                    'created_at' => now()->subDays($day),
                ]);
            }
        }

        // Draft site with 2 components
        $draftSite = Site::factory()->create([
            'user_id' => $user->id,
            'name' => 'Acme Internal',
            'slug' => 'acme-internal',
            'description' => 'Internal services monitoring.',
        ]);

        Component::factory()->create([
            'site_id' => $draftSite->id,
            'name' => 'Auth Service',
            'sort_order' => 0,
        ]);

        Component::factory()->create([
            'site_id' => $draftSite->id,
            'name' => 'Admin Panel',
            'sort_order' => 1,
        ]);
    }
}
