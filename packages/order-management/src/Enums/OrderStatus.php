<?php

namespace OrderManagement\Enums;

enum OrderStatus: string
{
    case Pending = 'Pending';
    case Late = 'Late';
    case Overdue = 'Overdue';
    case Approved = 'Approved';
    case Shipped = 'Shipped';
    case Cancelled = 'Cancelled';

    /**
     * Get the badge color for the status
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::Pending => 'secondary',
            self::Late => 'warning',
            self::Overdue => 'danger',
            self::Approved => 'info',
            self::Shipped => 'success',
            self::Cancelled => 'dark',
        };
    }

    /**
     * Check if the status is terminal (cannot be changed)
     */
    public function isTerminal(): bool
    {
        return match($this) {
            self::Shipped, self::Cancelled => true,
            default => false,
        };
    }

    /**
     * Check if the status allows editing
     */
    public function allowsEditing(): bool
    {
        return match($this) {
            self::Pending, self::Late, self::Overdue, self::Approved => true,
            default => false,
        };
    }
}
