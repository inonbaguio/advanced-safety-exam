<?php

namespace OrderManagement\Database\Factories;

use OrderManagement\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'approval_style' => $this->faker->randomElement(['Per User', 'Global']),
        ];
    }

    public function perUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_style' => 'Per User',
        ]);
    }

    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_style' => 'Global',
        ]);
    }
}
