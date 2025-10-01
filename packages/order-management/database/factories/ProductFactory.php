<?php

namespace OrderManagement\Database\Factories;

use OrderManagement\Models\Product;
use OrderManagement\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'template_id' => WorkflowTemplate::factory(),
            'owner_id' => null, // Can be set when creating
            'name' => $this->faker->words(3, true) . ' Product',
        ];
    }

    public function withOwner(int $ownerId): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $ownerId,
        ]);
    }

    public function withoutOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => null,
        ]);
    }
}
