<?php

namespace Database\Factories;

use App\Models\GuestLog;
use App\Models\Rt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GuestLog>
 */
class GuestLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rt_id' => Rt::factory(),
            'resident_id' => null,
            'guest_name' => $this->faker->name(),
            'guest_id_number' => $this->faker->optional()->numerify('################'),
            'origin' => $this->faker->city(),
            'purpose' => $this->faker->sentence(),
            'visit_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'arrival_time' => $this->faker->time('H:i'),
            'departure_time' => $this->faker->optional()->time('H:i'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
