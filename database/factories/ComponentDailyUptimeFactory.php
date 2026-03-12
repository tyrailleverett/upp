<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComponentDailyUptime>
 */
final class ComponentDailyUptimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'component_id' => Component::factory(),
            'date' => fake()->date(),
            'uptime_percentage' => fake()->randomFloat(2, 90, 100),
            'minutes_operational' => fake()->numberBetween(1380, 1440),
            'minutes_excluded_for_maintenance' => 0,
        ];
    }
}
