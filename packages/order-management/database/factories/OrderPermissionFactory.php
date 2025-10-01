<?php

namespace OrderManagement\Database\Factories;

use OrderManagement\Models\OrderPermission;
use OrderManagement\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderPermissionFactory extends Factory
{
    protected $model = OrderPermission::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => null, // Should be set when creating
            'module' => 'orders',
            'permission_type' => 'editor',
            'can_approve' => false,
            'can_edit' => true,
            'can_ship' => false,
            'can_cancel' => false,
        ];
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_type' => 'manager',
            'can_approve' => true,
            'can_edit' => true,
            'can_ship' => true,
            'can_cancel' => true,
        ]);
    }

    public function editor(): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_type' => 'editor',
            'can_approve' => false,
            'can_edit' => true,
            'can_ship' => false,
            'can_cancel' => false,
        ]);
    }

    public function approver(): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_type' => 'approver',
            'can_approve' => true,
            'can_edit' => false,
            'can_ship' => false,
            'can_cancel' => false,
        ]);
    }

    public function shipper(): static
    {
        return $this->state(fn (array $attributes) => [
            'permission_type' => 'shipper',
            'can_approve' => false,
            'can_edit' => false,
            'can_ship' => true,
            'can_cancel' => false,
        ]);
    }
}
