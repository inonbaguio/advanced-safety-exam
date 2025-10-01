<?php

namespace OrderManagement\Tests\Feature;

use OrderManagement\Models\Order;
use OrderManagement\Models\Product;
use OrderManagement\Models\WorkflowTemplate;
use OrderManagement\Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    /** @test */
    public function it_can_create_an_order()
    {
        $template = WorkflowTemplate::factory()->create();
        $product = Product::factory()->create(['template_id' => $template->template_id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', [
                'product_id' => $product->product_id,
                'template_id' => $template->template_id,
                'title' => 'Test Order',
                'notes' => 'Test notes',
                'dt_required' => now()->addDays(7)->toDateString(),
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'title' => 'Test Order',
        ]);
    }

    /** @test */
    public function it_can_retrieve_an_order()
    {
        $order = Order::factory()->assignedTo($this->user->id)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->order_id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'status',
                    'can' => [
                        'view',
                        'update',
                        'approve',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_update_an_order()
    {
        $order = Order::factory()->assignedTo($this->user->id)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/orders/{$order->order_id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Title',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => $order->order_id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_can_delete_an_order()
    {
        $order = Order::factory()->assignedTo($this->user->id)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/orders/{$order->order_id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('orders', [
            'order_id' => $order->order_id,
        ]);
    }

    /** @test */
    public function unauthorized_user_cannot_view_order()
    {
        $order = Order::factory()->create(); // Not assigned to user

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/orders/{$order->order_id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/orders', [
                'title' => 'Missing required fields',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id', 'template_id']);
    }
}
