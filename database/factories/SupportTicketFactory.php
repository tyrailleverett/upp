<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
final class SupportTicketFactory extends Factory
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
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'topic' => fake()->randomElement(TicketTopic::cases()),
        ];
    }

    /**
     * Indicate that the ticket has been resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolution' => fake()->paragraph(),
        ]);
    }
}
