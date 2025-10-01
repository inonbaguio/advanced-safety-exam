<?php

namespace OrderManagement\Http\Controllers;

use OrderManagement\Models\Order;
use OrderManagement\Services\OrderService;
use OrderManagement\Services\PermissionService;
use OrderManagement\Http\Requests\StoreOrderRequest;
use OrderManagement\Http\Requests\UpdateOrderRequest;
use OrderManagement\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PermissionService $permissionService
    ) {}

    /**
     * Display the specified order
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('view', $order);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Store a newly created order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Order created successfully',
            'data' => new OrderResource($order),
        ], 201);
    }

    /**
     * Update the specified order
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('update', $order);

        $order = $this->orderService->updateOrder(
            $order,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Order updated successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Remove the specified order
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('delete', $order);

        $this->orderService->deleteOrder($order, $request->user());

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }

    /**
     * Approve an order
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('approve', $order);

        $order = $this->orderService->approveOrder($order, $request->user());

        return response()->json([
            'message' => 'Order approved successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Ship an order
     */
    public function ship(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('ship', $order);

        $order = $this->orderService->shipOrder($order, $request->user());

        return response()->json([
            'message' => 'Order shipped successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Approve and ship an order in one action
     */
    public function approveAndShip(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('approve', $order);
        $this->authorize('ship', $order);

        $order = $this->orderService->approveAndShip($order, $request->user());

        return response()->json([
            'message' => 'Order approved and shipped successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Unapprove an order
     */
    public function unapprove(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('unapprove', $order);

        $order = $this->orderService->unapproveOrder($order, $request->user());

        return response()->json([
            'message' => 'Order unapproved successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('cancel', $order);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = $this->orderService->cancelOrder(
            $order,
            $request->user(),
            $request->input('reason')
        );

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Restore a cancelled order
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('restore', $order);

        $order = $this->orderService->restoreOrder($order, $request->user());

        return response()->json([
            'message' => 'Order restored successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Recall a shipment
     */
    public function recallShipment(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $this->authorize('ship', $order);

        $order = $this->orderService->recallShipment($order, $request->user());

        return response()->json([
            'message' => 'Shipment recalled successfully',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Get user permissions for an order
     */
    public function permissions(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrderDetails($id);

        $permissions = $this->permissionService->getUserPermissions(
            $request->user(),
            $order
        );

        return response()->json([
            'data' => $permissions,
        ]);
    }
}
