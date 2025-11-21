<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\Rt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RtOfficial>
 */
class RtOfficialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-5 years', 'now');

        return [
            'rt_id' => Rt::factory(),
            'resident_id' => Resident::factory(),
            'position' => 'Ketua RT',
            'started_at' => $start,
            'ended_at' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
