<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'plate_number' => strtoupper(fake()->bothify('? #### ??')),
            'brand' => fake()->randomElement(['goldenbird', 'executive']),
            'model' => fake()->randomElement(['Avanza', 'Innova', 'HiAce', 'Elf', 'APV', 'Fortuner', 'Pajero']),
            'capacity' => fake()->numberBetween(4, 30),
            'year' => fake()->year(),
            'status' => fake()->randomElement(['available', 'on_trip', 'maintenance', 'inactive']),
            'notes' => fake()->optional(0.3)->sentence(),
            'color' => fake()->safeColorName(),
            'transmission' => fake()->randomElement(['manual', 'automatic']),
        ];
    }
}
