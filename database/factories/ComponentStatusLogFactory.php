<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ComponentStatus;
use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComponentStatusLog>
 */
final class ComponentStatusLogFactory extends Factory
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
            'status' => ComponentStatus::Operational,
        ];
    }
}
