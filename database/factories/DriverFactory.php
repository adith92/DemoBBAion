<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->name(),
            'phone'          => fake()->phoneNumber(),
            'license_number' => strtoupper(fake()->bothify('SIM-########')),
            'status'         => fake()->randomElement(['available', 'on_trip', 'off_duty']),
            'notes'          => fake()->optional(0.3)->sentence(),
        ];
    }
}
