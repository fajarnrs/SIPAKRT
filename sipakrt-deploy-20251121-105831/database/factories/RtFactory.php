<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rt>
 */
class RtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'RT ' . str_pad((string) $this->faker->unique()->numberBetween(1, 20), 3, '0', STR_PAD_LEFT),
            'name' => $this->faker->unique()->streetName(),
            'leader_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
