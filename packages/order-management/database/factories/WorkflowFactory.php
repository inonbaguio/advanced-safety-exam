<?php

namespace OrderManagement\Database\Factories;

use OrderManagement\Models\Workflow;
use OrderManagement\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowFactory extends Factory
{
    protected $model = Workflow::class;

    public function definition(): array
    {
        return [
            'template_id' => WorkflowTemplate::factory(),
            'workflow_data' => null,
        ];
    }

    public function withCustomData(array $data): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_data' => $data,
        ]);
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_data' => [
                'frequency' => 'daily',
                'custom' => true,
            ],
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_data' => [
                'frequency' => 'weekly',
                'custom' => true,
            ],
        ]);
    }
}
