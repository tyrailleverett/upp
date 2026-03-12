<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceWindow>
 */
final class MaintenanceWindowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'scheduled_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(2),
            'completed_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subHours(3),
            'ends_at' => now()->subHour(),
            'completed_at' => now()->subHour(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->subHours(3),
            'ends_at' => now()->subHour(),
            'completed_at' => null,
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHours(2),
            'completed_at' => null,
        ]);
    }
}
