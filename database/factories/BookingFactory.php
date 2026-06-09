<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        $pickup = fake()->dateTimeBetween('-1 month', '+1 month');
        $dropoff = fake()->dateTimeBetween($pickup, (clone $pickup)->modify('+3 days'));

        return [
            'booking_number'   => 'BK-' . fake()->unique()->numerify('######'),
            'client_id'        => Client::factory(),
            'sales_id'         => User::factory(),
            'created_by'       => User::factory(),
            'vehicle_id'       => Vehicle::factory(),
            'driver_id'        => Driver::factory(),
            'pickup_datetime'  => $pickup,
            'dropoff_datetime' => $dropoff,
            'destination'      => fake()->city() . ', ' . fake()->state(),
            'vehicle_type'     => fake()->randomElement(['sedan', 'suv', 'van', 'bus', 'truck', 'minibus']),
            'price'            => fake()->randomFloat(2, 500000, 10000000),
            'status'           => fake()->randomElement(['pending', 'confirmed', 'on_trip', 'completed', 'cancelled']),
            'notes'            => fake()->optional(0.5)->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function confirmed(): static
    {
        return $this->state(['status' => 'confirmed']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
