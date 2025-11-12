<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['male', 'female']);
        $status = $this->faker->randomElement([
            Resident::STATUS_ACTIVE,
            Resident::STATUS_DECEASED,
            Resident::STATUS_MOVED,
            Resident::STATUS_TEMPORARY,
        ]);

        return [
            'household_id' => Household::factory(),
            'nik' => $this->faker->unique()->numerify('################'),
            'name' => $this->faker->name($gender),
            'relationship' => $this->faker->randomElement(['Kepala Keluarga', 'Istri', 'Anak', 'Saudara']),
            'gender' => $gender,
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-80 years', '-1 year'),
            'religion' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'education' => $this->faker->randomElement(['SD', 'SMP', 'SMA', 'Diploma', 'Sarjana']),
            'occupation' => $this->faker->randomElement(['Wiraswasta', 'Pegawai Negeri', 'Karyawan Swasta', 'Pelajar', 'Tidak Bekerja']),
            'marital_status' => $this->faker->randomElement(['Belum Kawin', 'Kawin', 'Cerai']),
            'email' => $this->faker->unique()->safeEmail(),
            'nationality' => 'WNI',
            'status' => $status,
            'status_effective_at' => $status === Resident::STATUS_ACTIVE
                ? null
                : $this->faker->dateTimeBetween('-5 years', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
