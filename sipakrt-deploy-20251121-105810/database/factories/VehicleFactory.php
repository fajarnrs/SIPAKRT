<?php

namespace Database\Factories;

use App\Models\Household;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
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
            'type' => $this->faker->randomElement(['Mobil', 'Motor', 'Sepeda', 'Pickup']),
            'brand' => $this->faker->company(),
            'model' => $this->faker->randomElement(['Standard', 'Sport', 'Family']),
            'license_plate' => strtoupper($this->faker->bothify('B #### ??')),
            'color' => $this->faker->safeColorName(),
            'year_of_purchase' => (int) $this->faker->year('-15 years'),
            'is_active' => $this->faker->boolean(85),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
