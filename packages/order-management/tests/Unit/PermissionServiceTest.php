<?php

namespace OrderManagement\Tests\Unit;

use OrderManagement\Models\Order;
use OrderManagement\Models\OrderPermission;
use OrderManagement\Services\PermissionService;
use OrderManagement\Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionService $service;
    protected User $user;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PermissionService::class);
        $this->user = $this->createUser();
        $this->order = Order::factory()->create();
    }

    /** @test */
    public function owner_can_view_order()
    {
        $this->order->assigned_to = $this->user->id;
        $this->order->save();

        $this->assertTrue($this->service->canView($this->user, $this->order));
    }

    /** @test */
    public function owner_can_edit_pending_order()
    {
        $this->order->assigned_to = $this->user->id;
        $this->order->save();

        $this->assertTrue($this->service->canEdit($this->user, $this->order));
    }

    /** @test */
    public function cannot_edit_shipped_order()
    {
        $this->order->assigned_to = $this->user->id;
        $this->order->dt_shipped = now();
        $this->order->save();

        $this->assertFalse($this->service->canEdit($this->user, $this->order));
    }

    /** @test */
    public function owner_can_approve_order()
    {
        $this->order->assigned_to = $this->user->id;
        $this->order->save();

        $this->assertTrue($this->service->canApprove($this->user, $this->order));
    }

    /** @test */
    public function cannot_approve_already_approved_order()
    {
        $this->order->assigned_to = $this->user->id;
        $this->order->dt_approved = now();
        $this->order->save();

        $this->assertFalse($this->service->canApprove($this->user, $this->order));
    }

    /** @test */
    public function user_with_permission_can_ship_order()
    {
        $this->order->dt_approved = now();
        $this->order->save();

        OrderPermission::factory()->create([
            'order_id' => $this->order->order_id,
            'user_id' => $this->user->id,
            'can_ship' => true,
        ]);

        $this->assertTrue($this->service->canShip($this->user, $this->order));
    }

    /** @test */
    public function cannot_ship_unapproved_order()
    {
        OrderPermission::factory()->create([
            'order_id' => $this->order->order_id,
            'user_id' => $this->user->id,
            'can_ship' => true,
        ]);

        $this->assertFalse($this->service->canShip($this->user, $this->order));
    }

    /** @test */
    public function user_with_permission_can_cancel_order()
    {
        OrderPermission::factory()->create([
            'order_id' => $this->order->order_id,
            'user_id' => $this->user->id,
            'can_cancel' => true,
        ]);

        $this->assertTrue($this->service->canCancel($this->user, $this->order));
    }

    /** @test */
    public function cannot_cancel_shipped_order()
    {
        $this->order->dt_shipped = now();
        $this->order->save();

        OrderPermission::factory()->create([
            'order_id' => $this->order->order_id,
            'user_id' => $this->user->id,
            'can_cancel' => true,
        ]);

        $this->assertFalse($this->service->canCancel($this->user, $this->order));
    }

    /** @test */
    public function it_gets_all_user_permissions()
    {
        $this->order->assigned_to = $this->user->id;
        $this->order->save();

        $permissions = $this->service->getUserPermissions($this->user, $this->order);

        $this->assertIsArray($permissions);
        $this->assertArrayHasKey('view', $permissions);
        $this->assertArrayHasKey('edit', $permissions);
        $this->assertArrayHasKey('approve', $permissions);
        $this->assertArrayHasKey('is_owner', $permissions);
        $this->assertTrue($permissions['is_owner']);
    }

    /** @test */
    public function it_grants_permission_to_user()
    {
        $permission = $this->service->grantPermission(
            $this->order,
            $this->user,
            'orders',
            'manager',
            ['can_approve' => true, 'can_ship' => true]
        );

        $this->assertInstanceOf(OrderPermission::class, $permission);
        $this->assertTrue($permission->can_approve);
        $this->assertTrue($permission->can_ship);
    }

    /** @test */
    public function it_revokes_user_permissions()
    {
        OrderPermission::factory()->create([
            'order_id' => $this->order->order_id,
            'user_id' => $this->user->id,
        ]);

        $result = $this->service->revokePermissions($this->order, $this->user);

        $this->assertTrue($result);
        $this->assertEquals(0, OrderPermission::where('order_id', $this->order->order_id)
            ->where('user_id', $this->user->id)
            ->count());
    }
}
