<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ComponentStatus;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Component>
 */
final class ComponentFactory extends Factory
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
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'group' => null,
            'status' => ComponentStatus::Operational,
            'sort_order' => 0,
        ];
    }

    public function degraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComponentStatus::DegradedPerformance,
        ]);
    }

    public function partialOutage(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComponentStatus::PartialOutage,
        ]);
    }

    public function majorOutage(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComponentStatus::MajorOutage,
        ]);
    }

    public function underMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComponentStatus::UnderMaintenance,
        ]);
    }

    public function inGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }
}
