<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => 'sales',
            'manager_id'        => null,
            'sales_level'       => 'junior',
            'remember_token'    => Str::random(10),
        ];
    }

    public function sales(): static
    {
        return $this->state(['role' => 'sales']);
    }

    public function manager(): static
    {
        return $this->state(['role' => 'manager']);
    }

    public function gm(): static
    {
        return $this->state(['role' => 'gm']);
    }

    public function finance(): static
    {
        return $this->state(['role' => 'finance']);
    }
}
