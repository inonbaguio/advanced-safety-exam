<?php

namespace OrderManagement\Database\Factories;

use OrderManagement\Models\Order;
use OrderManagement\Models\Product;
use OrderManagement\Models\WorkflowTemplate;
use OrderManagement\Models\Workflow;
use OrderManagement\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $template = WorkflowTemplate::factory()->create();
        $product = Product::factory()->create(['template_id' => $template->template_id]);

        return [
            'customer_id' => null,
            'store_id' => null,
            'product_id' => $product->product_id,
            'workflow_id' => null,
            'template_id' => $template->template_id,
            'title' => $this->faker->sentence(4),
            'notes' => $this->faker->optional()->paragraph(),
            'assigned_to' => null,
            'created_by' => null,
            'dt_created' => now(),
            'dt_required' => now()->addDays(7),
            'dt_deadline' => now()->addDays(14),
            'dt_completed' => null,
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
            'completed_by' => null,
            'approved_by' => null,
            'shipped_by' => null,
            'cancelled_by' => null,
            'cancel_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);
    }

    public function approved(int $approverId): static
    {
        return $this->state(fn (array $attributes) => [
            'dt_approved' => now()->subDays(1),
            'approved_by' => $approverId,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);
    }

    public function shipped(int $shipperId, ?int $approverId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'dt_approved' => now()->subDays(2),
            'approved_by' => $approverId ?? $shipperId,
            'dt_shipped' => now()->subDays(1),
            'shipped_by' => $shipperId,
            'dt_cancelled' => null,
        ]);
    }

    public function cancelled(int $cancellerId, ?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'dt_cancelled' => now()->subDays(1),
            'cancelled_by' => $cancellerId,
            'cancel_reason' => $reason ?? 'Order cancelled',
            'dt_shipped' => null,
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'dt_required' => now()->subDays(2),
            'dt_deadline' => now()->addDays(3),
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'dt_required' => now()->subDays(10),
            'dt_deadline' => now()->subDays(5),
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);
    }

    public function assignedTo(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $userId,
        ]);
    }

    public function withStore(int $storeId): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => $storeId,
        ]);
    }

    public function withWorkflow(int $workflowId): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_id' => $workflowId,
        ]);
    }
}
