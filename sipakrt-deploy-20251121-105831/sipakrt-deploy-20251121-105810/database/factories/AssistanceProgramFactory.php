<?php

namespace Database\Factories;

use App\Models\Rt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssistanceProgram>
 */
class AssistanceProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 years', 'now');
        $end = $this->faker->optional()->dateTimeBetween($start, '+1 years');

        return [
            'rt_id' => Rt::factory(),
            'name' => 'Program ' . $this->faker->words(2, true),
            'category' => $this->faker->randomElement(['Beras', 'Tunai', 'Kesehatan', 'Beasiswa']),
            'source' => $this->faker->randomElement(['internal', 'external']),
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => $end ? $end >= now() : true,
            'description' => $this->faker->sentence(),
        ];
    }
}
