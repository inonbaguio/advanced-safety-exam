<?php

namespace OrderManagement\Database\Factories;

use OrderManagement\Models\Store;
use OrderManagement\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->company() . ' Store',
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
