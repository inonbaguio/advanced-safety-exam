<?php

namespace OrderManagement\Services;

use OrderManagement\Models\Order;
use OrderManagement\Repositories\OrderRepository;
use OrderManagement\Events\OrderApproved;
use OrderManagement\Events\OrderShipped;
use OrderManagement\Events\OrderCancelled;
use OrderManagement\Exceptions\OrderException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepository $repository,
        protected PermissionService $permissionService,
        protected StatusCalculator $statusCalculator
    ) {}

    /**
     * Create a new order
     */
    public function createOrder(array $data, User $creator): Order
    {
        return DB::transaction(function () use ($data, $creator) {
            $data['created_by'] = $creator->id;
            $data['dt_created'] = now();

            return $this->repository->create($data);
        });
    }

    /**
     * Update an order
     */
    public function updateOrder(Order $order, array $data, User $user): Order
    {
        if (!$this->permissionService->canEdit($user, $order)) {
            throw new OrderException('You do not have permission to edit this order.');
        }

        DB::transaction(function () use ($order, $data) {
            $this->repository->update($order, $data);
        });

        return $order->fresh();
    }

    /**
     * Approve an order
     */
    public function approveOrder(Order $order, User $approver): Order
    {
        if (!$this->permissionService->canApprove($approver, $order)) {
            throw new OrderException('You do not have permission to approve this order.');
        }

        if ($order->dt_approved) {
            throw new OrderException('This order has already been approved.');
        }

        DB::transaction(function () use ($order, $approver) {
            $this->repository->update($order, [
                'dt_approved' => now(),
                'approved_by' => $approver->id,
            ]);

            event(new OrderApproved($order, $approver));
        });

        return $order->fresh();
    }

    /**
     * Approve and ship an order in one action
     */
    public function approveAndShip(Order $order, User $user): Order
    {
        if (!$this->permissionService->canApprove($user, $order)) {
            throw new OrderException('You do not have permission to approve this order.');
        }

        if (!$this->permissionService->canShip($user, $order)) {
            throw new OrderException('You do not have permission to ship this order.');
        }

        DB::transaction(function () use ($order, $user) {
            // First approve
            $this->repository->update($order, [
                'dt_approved' => now(),
                'approved_by' => $user->id,
            ]);

            event(new OrderApproved($order, $user));

            // Then ship
            $this->repository->update($order, [
                'dt_shipped' => now(),
                'shipped_by' => $user->id,
            ]);

            event(new OrderShipped($order, $user));
        });

        return $order->fresh();
    }

    /**
     * Unapprove an order
     */
    public function unapproveOrder(Order $order, User $user): Order
    {
        if (!$this->permissionService->canUnapprove($user, $order)) {
            throw new OrderException('You do not have permission to unapprove this order.');
        }

        DB::transaction(function () use ($order) {
            $this->repository->update($order, [
                'dt_approved' => null,
                'approved_by' => null,
            ]);
        });

        return $order->fresh();
    }

    /**
     * Ship an order
     */
    public function shipOrder(Order $order, User $shipper): Order
    {
        if (!$this->permissionService->canShip($shipper, $order)) {
            throw new OrderException('You do not have permission to ship this order.');
        }

        if (!$order->dt_approved) {
            throw new OrderException('Order must be approved before shipping.');
        }

        DB::transaction(function () use ($order, $shipper) {
            $this->repository->update($order, [
                'dt_shipped' => now(),
                'shipped_by' => $shipper->id,
            ]);

            event(new OrderShipped($order, $shipper));
        });

        return $order->fresh();
    }

    /**
     * Recall a shipment
     */
    public function recallShipment(Order $order, User $user): Order
    {
        if (!$this->permissionService->canShip($user, $order)) {
            throw new OrderException('You do not have permission to recall this shipment.');
        }

        if (!$order->dt_shipped) {
            throw new OrderException('This order has not been shipped.');
        }

        DB::transaction(function () use ($order) {
            $this->repository->update($order, [
                'dt_shipped' => null,
                'shipped_by' => null,
            ]);
        });

        return $order->fresh();
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order, User $canceller, ?string $reason = null): Order
    {
        if (!$this->permissionService->canCancel($canceller, $order)) {
            throw new OrderException('You do not have permission to cancel this order.');
        }

        DB::transaction(function () use ($order, $canceller, $reason) {
            $this->repository->update($order, [
                'dt_cancelled' => now(),
                'cancelled_by' => $canceller->id,
                'cancel_reason' => $reason,
            ]);

            event(new OrderCancelled($order, $canceller, $reason));
        });

        return $order->fresh();
    }

    /**
     * Restore a cancelled order
     */
    public function restoreOrder(Order $order, User $user): Order
    {
        if (!$this->permissionService->canRestore($user, $order)) {
            throw new OrderException('You do not have permission to restore this order.');
        }

        DB::transaction(function () use ($order) {
            $this->repository->update($order, [
                'dt_cancelled' => null,
                'cancelled_by' => null,
                'cancel_reason' => null,
            ]);
        });

        return $order->fresh();
    }

    /**
     * Delete an order
     */
    public function deleteOrder(Order $order, User $user): bool
    {
        // Add permission check based on your requirements
        if (!$this->permissionService->canEdit($user, $order)) {
            throw new OrderException('You do not have permission to delete this order.');
        }

        return DB::transaction(function () use ($order) {
            return $this->repository->delete($order);
        });
    }

    /**
     * Get order with full details
     */
    public function getOrderDetails(int $orderId): Order
    {
        $order = $this->repository->findWithRelations($orderId);

        if (!$order) {
            throw new OrderException('Order not found.');
        }

        return $order;
    }

    /**
     * Get order status
     */
    public function getOrderStatus(Order $order): array
    {
        return $this->statusCalculator->getStatusBadge($order);
    }
}
