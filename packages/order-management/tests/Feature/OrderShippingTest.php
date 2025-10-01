<?php

namespace OrderManagement\Tests\Feature;

use OrderManagement\Models\Order;
use OrderManagement\Models\OrderPermission;
use OrderManagement\Events\OrderShipped;
use OrderManagement\Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class OrderShippingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    /** @test */
    public function can_ship_approved_order_with_permission()
    {
        Event::fake();

        $approver = $this->createUser();
        $order = Order::factory()->approved($approver->id)->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_ship' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/ship");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order shipped successfully',
            ]);

        $order->refresh();
        $this->assertNotNull($order->dt_shipped);
        $this->assertEquals($this->user->id, $order->shipped_by);

        Event::assertDispatched(OrderShipped::class);
    }

    /** @test */
    public function cannot_ship_unapproved_order()
    {
        $order = Order::factory()->pending()->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_ship' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/ship");

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_ship_without_permission()
    {
        $approver = $this->createUser();
        $order = Order::factory()->approved($approver->id)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/ship");

        $response->assertStatus(403);
    }

    /** @test */
    public function can_recall_shipment()
    {
        $order = Order::factory()->shipped($this->user->id)->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_ship' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/recall-shipment");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Shipment recalled successfully',
            ]);

        $order->refresh();
        $this->assertNull($order->dt_shipped);
        $this->assertNull($order->shipped_by);
    }

    /** @test */
    public function cannot_recall_unshipped_order()
    {
        $order = Order::factory()->approved($this->user->id)->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_ship' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/recall-shipment");

        $response->assertStatus(500); // OrderException
    }
}
