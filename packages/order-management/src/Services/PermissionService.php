<?php

namespace OrderManagement\Services;

use OrderManagement\Models\Order;
use OrderManagement\Models\OrderPermission;
use App\Models\User;

class PermissionService
{
    /**
     * Check if user can view the order
     */
    public function canView(User $user, Order $order): bool
    {
        // Owner can always view
        if ($order->belongsToUser($user->id)) {
            return true;
        }

        // Check explicit permissions
        return $this->hasPermission($user, $order, 'can_edit');
    }

    /**
     * Check if user can edit the order
     */
    public function canEdit(User $user, Order $order): bool
    {
        // Cannot edit if shipped or cancelled (unless special permission)
        if ($order->dt_shipped || $order->dt_cancelled) {
            return false;
        }

        // Cannot edit if past deadline (unless user has permission)
        if ($order->isPastDeadline() && !$this->canEditOverdue($user)) {
            return false;
        }

        // Owner can edit
        if ($order->belongsToUser($user->id)) {
            return true;
        }

        // Check explicit edit permission
        return $this->hasPermission($user, $order, 'can_edit');
    }

    /**
     * Check if user can approve the order
     */
    public function canApprove(User $user, Order $order): bool
    {
        // Cannot approve if already approved
        if ($order->dt_approved) {
            return false;
        }

        // Cannot approve if cancelled or shipped
        if ($order->dt_cancelled || $order->dt_shipped) {
            return false;
        }

        // Owner can approve their own orders
        if ($order->belongsToUser($user->id)) {
            return true;
        }

        // Check explicit approval permission
        return $this->hasPermission($user, $order, 'can_approve');
    }

    /**
     * Check if user can ship the order
     */
    public function canShip(User $user, Order $order): bool
    {
        // Cannot ship if not approved
        if (!$order->dt_approved) {
            return false;
        }

        // Cannot ship if already shipped
        if ($order->dt_shipped) {
            return false;
        }

        // Cannot ship if cancelled
        if ($order->dt_cancelled) {
            return false;
        }

        return $this->hasPermission($user, $order, 'can_ship');
    }

    /**
     * Check if user can cancel the order
     */
    public function canCancel(User $user, Order $order): bool
    {
        // Cannot cancel if already cancelled
        if ($order->dt_cancelled) {
            return false;
        }

        // Cannot cancel if shipped
        if ($order->dt_shipped) {
            return false;
        }

        return $this->hasPermission($user, $order, 'can_cancel');
    }

    /**
     * Check if user can unapprove the order
     */
    public function canUnapprove(User $user, Order $order): bool
    {
        // Must be approved first
        if (!$order->dt_approved) {
            return false;
        }

        // Cannot unapprove if shipped
        if ($order->dt_shipped) {
            return false;
        }

        // Check deadline unless user has overdue permission
        if ($order->isPastDeadline() && !$this->canEditOverdue($user)) {
            return false;
        }

        // Owner can unapprove
        if ($order->belongsToUser($user->id)) {
            return true;
        }

        // Those who can approve can also unapprove
        return $this->hasPermission($user, $order, 'can_approve') ||
               $this->hasPermission($user, $order, 'can_ship');
    }

    /**
     * Check if user can restore a cancelled order
     */
    public function canRestore(User $user, Order $order): bool
    {
        if (!$order->dt_cancelled) {
            return false;
        }

        return $this->hasPermission($user, $order, 'can_cancel');
    }

    /**
     * Get all permissions for a user on an order
     */
    public function getUserPermissions(User $user, Order $order): array
    {
        return [
            'view' => $this->canView($user, $order),
            'edit' => $this->canEdit($user, $order),
            'approve' => $this->canApprove($user, $order),
            'ship' => $this->canShip($user, $order),
            'cancel' => $this->canCancel($user, $order),
            'unapprove' => $this->canUnapprove($user, $order),
            'restore' => $this->canRestore($user, $order),
            'is_owner' => $order->belongsToUser($user->id),
        ];
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(User $user, Order $order, string $permission): bool
    {
        return OrderPermission::where('order_id', $order->order_id)
            ->where('user_id', $user->id)
            ->where($permission, true)
            ->exists();
    }

    /**
     * Check if user can edit overdue orders
     */
    protected function canEditOverdue(User $user): bool
    {
        // This would check against user permissions/roles
        // Implement according to your application's permission system
        return $user->hasPermissionTo(config('order-management.permissions.edit_overdue', 'edit_overdue_orders'));
    }

    /**
     * Grant permission to user for an order
     */
    public function grantPermission(
        Order $order,
        User $user,
        string $module,
        string $permissionType,
        array $permissions = []
    ): OrderPermission {
        return OrderPermission::updateOrCreate(
            [
                'order_id' => $order->order_id,
                'user_id' => $user->id,
                'module' => $module,
            ],
            array_merge([
                'permission_type' => $permissionType,
            ], $permissions)
        );
    }

    /**
     * Revoke all permissions for a user on an order
     */
    public function revokePermissions(Order $order, User $user, ?string $module = null): bool
    {
        $query = OrderPermission::where('order_id', $order->order_id)
            ->where('user_id', $user->id);

        if ($module) {
            $query->where('module', $module);
        }

        return $query->delete() > 0;
    }
}
