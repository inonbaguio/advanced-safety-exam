<?php

namespace OrderManagement\Database\Factories;

use OrderManagement\Models\WorkflowTemplate;
use OrderManagement\Models\Company;
use OrderManagement\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowTemplateFactory extends Factory
{
    protected $model = WorkflowTemplate::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'store_id' => null,
            'template_name' => $this->faker->words(3, true) . ' Template',
            'workflow_name' => $this->faker->words(2, true) . ' Workflow',
            'icon' => $this->faker->randomElement(['file', 'clipboard', 'check-circle', 'briefcase']),
            'intro_text' => $this->faker->sentence(),
            'settings' => [
                'workflow' => [
                    'frequency' => 'one-time',
                    'custom_allowed' => false,
                ],
            ],
            'approval_required' => true,
        ];
    }

    public function withStore(): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => Store::factory(),
        ]);
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [
                'workflow' => [
                    'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
                    'custom_allowed' => true,
                ],
            ],
        ]);
    }

    public function noApprovalRequired(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_required' => false,
        ]);
    }
}
