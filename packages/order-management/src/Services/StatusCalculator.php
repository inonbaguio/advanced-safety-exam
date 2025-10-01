<?php

namespace OrderManagement\Services;

use OrderManagement\Models\Order;
use OrderManagement\Enums\OrderStatus;

class StatusCalculator
{
    /**
     * Calculate the current status of an order
     *
     * Status priority (from legacy code lines 13-20):
     * 1. Cancelled (if both cancelled and shipped timestamps exist)
     * 2. Shipped (if shipped timestamp exists)
     * 3. Approved (if approved timestamp exists)
     * 4. Overdue (if deadline exists and passed)
     * 5. Late (if required date exists and passed)
     * 6. Pending (default)
     */
    public function calculate(Order $order): OrderStatus
    {
        // Cancelled takes precedence over shipped
        if ($order->dt_cancelled && $order->dt_shipped) {
            return OrderStatus::Cancelled;
        }

        // Check if shipped
        if ($order->dt_shipped) {
            return OrderStatus::Shipped;
        }

        // Check if approved
        if ($order->dt_approved) {
            return OrderStatus::Approved;
        }

        // Check if past deadline (Overdue)
        if ($order->dt_deadline && now()->gt($order->dt_deadline)) {
            return OrderStatus::Overdue;
        }

        // Check if past required date (Late)
        if ($order->dt_required && now()->gt($order->dt_required)) {
            return OrderStatus::Late;
        }

        // Default status
        return OrderStatus::Pending;
    }

    /**
     * Get status badge information
     */
    public function getStatusBadge(Order $order): array
    {
        $status = $this->calculate($order);

        return [
            'status' => $status->value,
            'color' => $status->badgeColor(),
            'label' => ucwords($status->value),
        ];
    }

    /**
     * Check if order can be edited based on status
     */
    public function canEdit(Order $order): bool
    {
        $status = $this->calculate($order);
        return $status->allowsEditing();
    }

    /**
     * Check if order status is terminal (cannot be changed)
     */
    public function isTerminal(Order $order): bool
    {
        $status = $this->calculate($order);
        return $status->isTerminal();
    }

    /**
     * Check if order is in warning state (approaching deadline)
     */
    public function isInWarningState(Order $order): bool
    {
        $status = $this->calculate($order);

        if ($status === OrderStatus::Pending) {
            return $order->isRequiredDateApproaching();
        }

        return in_array($status, [OrderStatus::Late, OrderStatus::Overdue]);
    }
}
