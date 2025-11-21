<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\Rt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Household>
 */
class HouseholdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['male', 'female']);

        return [
            'rt_id' => Rt::factory(),
            'family_card_number' => $this->faker->unique()->numerify('################'),
            'head_name' => $this->faker->name($gender),
            'head_nik' => $this->faker->unique()->numerify('################'),
            'head_gender' => $gender,
            'head_birth_place' => $this->faker->city(),
            'head_birth_date' => $this->faker->dateTimeBetween('-70 years', '-20 years'),
            'head_religion' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'head_education' => $this->faker->randomElement(['SD', 'SMP', 'SMA', 'Diploma', 'Sarjana']),
            'head_occupation' => $this->faker->randomElement(['Wiraswasta', 'Pegawai Negeri', 'Karyawan Swasta', 'Petani']),
            'head_marital_status' => 'Kawin',
            'head_nationality' => 'WNI',
            'head_status' => Resident::STATUS_ACTIVE,
            'head_notes' => $this->faker->optional()->sentence(),
            'address' => $this->faker->streetAddress(),
            'issued_at' => $this->faker->optional()->dateTimeBetween('-5 years', 'now'),
        ];
    }
}
