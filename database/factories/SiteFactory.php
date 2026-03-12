<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SiteVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Site>
 */
final class SiteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'visibility' => SiteVisibility::Draft,
            'accent_color' => fake()->hexColor(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => SiteVisibility::Published,
            'published_at' => now(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => SiteVisibility::Suspended,
            'suspended_at' => now(),
        ]);
    }

    public function withCustomDomain(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_domain' => fake()->domainName(),
        ]);
    }
}
