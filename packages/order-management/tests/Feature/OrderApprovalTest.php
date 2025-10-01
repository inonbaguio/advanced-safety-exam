<?php

namespace OrderManagement\Tests\Feature;

use OrderManagement\Models\Order;
use OrderManagement\Models\OrderPermission;
use OrderManagement\Events\OrderApproved;
use OrderManagement\Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class OrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    /** @test */
    public function owner_can_approve_their_order()
    {
        Event::fake();

        $order = Order::factory()->assignedTo($this->user->id)->pending()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order approved successfully',
            ]);

        $order->refresh();
        $this->assertNotNull($order->dt_approved);
        $this->assertEquals($this->user->id, $order->approved_by);

        Event::assertDispatched(OrderApproved::class);
    }

    /** @test */
    public function user_with_permission_can_approve_order()
    {
        $order = Order::factory()->pending()->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_approve' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/approve");

        $response->assertStatus(200);

        $order->refresh();
        $this->assertNotNull($order->dt_approved);
    }

    /** @test */
    public function cannot_approve_already_approved_order()
    {
        $approver = $this->createUser();
        $order = Order::factory()->approved($approver->id)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/approve");

        $response->assertStatus(403);
    }

    /** @test */
    public function can_unapprove_order()
    {
        $order = Order::factory()
            ->assignedTo($this->user->id)
            ->approved($this->user->id)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/unapprove");

        $response->assertStatus(200);

        $order->refresh();
        $this->assertNull($order->dt_approved);
        $this->assertNull($order->approved_by);
    }

    /** @test */
    public function cannot_unapprove_shipped_order()
    {
        $order = Order::factory()
            ->assignedTo($this->user->id)
            ->shipped($this->user->id)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/unapprove");

        $response->assertStatus(403);
    }

    /** @test */
    public function can_approve_and_ship_in_one_action()
    {
        Event::fake();

        $order = Order::factory()->assignedTo($this->user->id)->pending()->create();

        OrderPermission::factory()->create([
            'order_id' => $order->order_id,
            'user_id' => $this->user->id,
            'can_approve' => true,
            'can_ship' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/orders/{$order->order_id}/approve-and-ship");

        $response->assertStatus(200);

        $order->refresh();
        $this->assertNotNull($order->dt_approved);
        $this->assertNotNull($order->dt_shipped);

        Event::assertDispatched(OrderApproved::class);
    }
}
