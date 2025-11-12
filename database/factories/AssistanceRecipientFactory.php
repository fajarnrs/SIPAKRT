<?php

namespace Database\Factories;

use App\Models\AssistanceProgram;
use App\Models\Household;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssistanceRecipient>
 */
class AssistanceRecipientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['diajukan', 'disetujui', 'ditolak', 'diterima']);

        return [
            'assistance_program_id' => AssistanceProgram::factory(),
            'household_id' => Household::factory(),
            'resident_id' => Resident::factory(),
            'received_at' => $this->faker->optional()->dateTimeBetween('-1 years', 'now'),
            'amount' => $this->faker->optional()->randomFloat(2, 50000, 2000000),
            'status' => $status,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
