<?php

namespace OrderManagement\Repositories;

use OrderManagement\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    /**
     * Get order by ID with relationships
     */
    public function findWithRelations(int $id): ?Order
    {
        return Order::with([
            'product.owner',
            'product.template',
            'workflow.template',
            'template.store.company',
            'template.company',
            'assignedUser',
            'approver',
            'shipper',
            'canceller',
            'completer',
            'permissions'
        ])->find($id);
    }

    /**
     * Get orders assigned to user
     */
    public function getAssignedToUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['product', 'template'])
            ->assignedTo($userId)
            ->orderBy('dt_required', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get pending orders
     */
    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['product', 'assignedUser'])
            ->pending()
            ->orderBy('dt_required', 'asc')
            ->paginate($perPage);
    }

    /**
     * Create a new order
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update an order
     */
    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    /**
     * Delete an order
     */
    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    /**
     * Get orders by product
     */
    public function getByProduct(int $productId): Collection
    {
        return Order::where('product_id', $productId)
            ->with(['assignedUser', 'template'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get orders that are overdue
     */
    public function getOverdue(): Collection
    {
        return Order::whereNotNull('dt_deadline')
            ->where('dt_deadline', '<', now())
            ->whereNull('dt_completed')
            ->whereNull('dt_cancelled')
            ->with(['assignedUser', 'product'])
            ->get();
    }

    /**
     * Get orders approaching deadline
     */
    public function getApproachingDeadline(int $days = 3): Collection
    {
        return Order::whereNotNull('dt_required')
            ->whereBetween('dt_required', [now(), now()->addDays($days)])
            ->whereNull('dt_completed')
            ->whereNull('dt_cancelled')
            ->with(['assignedUser', 'product'])
            ->get();
    }
}
