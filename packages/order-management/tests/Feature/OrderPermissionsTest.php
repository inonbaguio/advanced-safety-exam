<?php

namespace OrderManagement\Tests\Feature;

use OrderManagement\Models\Order;
use OrderManagement\Models\OrderPermission;
use OrderManagement\Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    /** @test */
    public function it_returns_user_permissions_for_order()
    {
        $order = Order::factory()->assignedTo($this->user->id)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->order_id}/permissions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'view',
                    'edit',
                    'approve',
                    'ship',
                    'cancel',
                    'unapprove',
                    'restore',
                    'is_owner',
                ],
            ])
            ->assertJson([
                'data' => [
                    'is_owner' => true,
                    'view' => true,
                ],
            ]);
    }

    /** @test */
    public function manager_has_all_permissions()
    {
        $order = Order::factory()->pending()->create();

        OrderPermission::factory()->manager()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->order_id}/permissions");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'view' => true,
                    'edit' => true,
                    'approve' => true,
                    'cancel' => true,
                ],
            ]);
    }

    /** @test */
    public function editor_can_only_edit()
    {
        $order = Order::factory()->pending()->create();

        OrderPermission::factory()->editor()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->order_id}/permissions");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'view' => true,
                    'edit' => true,
                    'approve' => false,
                    'ship' => false,
                ],
            ]);
    }

    /** @test */
    public function order_resource_includes_permission_flags()
    {
        $order = Order::factory()->assignedTo($this->user->id)->pending()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->order_id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'can' => [
                        'view',
                        'update',
                        'delete',
                        'approve',
                        'ship',
                        'cancel',
                    ],
                ],
            ]);
    }
}
