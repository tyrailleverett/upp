<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IncidentStatus;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
final class IncidentFactory extends Factory
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
            'title' => fake()->sentence(),
            'status' => IncidentStatus::Investigating,
        ];
    }

    public function identified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncidentStatus::Identified,
        ]);
    }

    public function monitoring(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncidentStatus::Monitoring,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => IncidentStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }

    public function withPostmortem(): static
    {
        return $this->state(fn (array $attributes) => [
            'postmortem' => fake()->paragraphs(3, true),
        ]);
    }
}
