<?php

namespace OrderManagement\Policies;

use OrderManagement\Models\Order;
use OrderManagement\Services\PermissionService;
use App\Models\User;

class OrderPolicy
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Determine if the user can view the order
     */
    public function view(User $user, Order $order): bool
    {
        return $this->permissionService->canView($user, $order);
    }

    /**
     * Determine if the user can update the order
     */
    public function update(User $user, Order $order): bool
    {
        return $this->permissionService->canEdit($user, $order);
    }

    /**
     * Determine if the user can delete the order
     */
    public function delete(User $user, Order $order): bool
    {
        return $this->permissionService->canEdit($user, $order);
    }

    /**
     * Determine if the user can approve the order
     */
    public function approve(User $user, Order $order): bool
    {
        return $this->permissionService->canApprove($user, $order);
    }

    /**
     * Determine if the user can unapprove the order
     */
    public function unapprove(User $user, Order $order): bool
    {
        return $this->permissionService->canUnapprove($user, $order);
    }

    /**
     * Determine if the user can ship the order
     */
    public function ship(User $user, Order $order): bool
    {
        return $this->permissionService->canShip($user, $order);
    }

    /**
     * Determine if the user can cancel the order
     */
    public function cancel(User $user, Order $order): bool
    {
        return $this->permissionService->canCancel($user, $order);
    }

    /**
     * Determine if the user can restore the order
     */
    public function restore(User $user, Order $order): bool
    {
        return $this->permissionService->canRestore($user, $order);
    }
}
