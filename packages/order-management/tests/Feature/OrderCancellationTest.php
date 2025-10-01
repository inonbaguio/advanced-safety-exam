<?php

namespace OrderManagement\Tests\Feature;

use OrderManagement\Models\Order;
use OrderManagement\Models\OrderPermission;
use OrderManagement\Events\OrderCancelled;
use OrderManagement\Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    /** @test */
    public function can_cancel_order_with_permission()
    {
        Event::fake();

        $order = Order::factory()->pending()->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_cancel' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/cancel", [
                'reason' => 'Customer requested cancellation',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order cancelled successfully',
            ]);

        $order->refresh();
        $this->assertNotNull($order->dt_cancelled);
        $this->assertEquals($this->user->id, $order->cancelled_by);
        $this->assertEquals('Customer requested cancellation', $order->cancel_reason);

        Event::assertDispatched(OrderCancelled::class);
    }

    /** @test */
    public function cannot_cancel_shipped_order()
    {
        $order = Order::factory()->shipped($this->user->id)->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_cancel' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/cancel");

        $response->assertStatus(403);
    }

    /** @test */
    public function can_restore_cancelled_order()
    {
        $order = Order::factory()->cancelled($this->user->id, 'Test reason')->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_cancel' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order restored successfully',
            ]);

        $order->refresh();
        $this->assertNull($order->dt_cancelled);
        $this->assertNull($order->cancelled_by);
        $this->assertNull($order->cancel_reason);
    }

    /** @test */
    public function cannot_restore_non_cancelled_order()
    {
        $order = Order::factory()->pending()->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_cancel' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/restore");

        $response->assertStatus(403);
    }

    /** @test */
    public function cancellation_reason_is_optional()
    {
        $order = Order::factory()->pending()->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_cancel' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/cancel");

        $response->assertStatus(200);

        $order->refresh();
        $this->assertNotNull($order->dt_cancelled);
    }
}
