<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HouseholdMovement>
 */
class HouseholdMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'type' => $this->faker->randomElement(['pindah_masuk', 'pindah_keluar', 'meninggal', 'lainnya']),
            'event_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'destination' => $this->faker->optional()->city(),
            'details' => $this->faker->optional()->paragraph(),
            'processed_by' => User::factory(),
            'status' => $this->faker->randomElement(['draft', 'diproses', 'selesai']),
        ];
    }
}
