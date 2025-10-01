<?php

namespace OrderManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OrderManagement\Services\StatusCalculator;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $statusCalculator = app(StatusCalculator::class);
        $status = $statusCalculator->calculate($this->resource);

        return [
            'id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'store_id' => $this->store_id,
            'product_id' => $this->product_id,
            'workflow_id' => $this->workflow_id,
            'template_id' => $this->template_id,
            'title' => $this->title,
            'notes' => $this->notes,

            // Dates
            'dt_created' => $this->dt_created?->toIso8601String(),
            'dt_required' => $this->dt_required?->toIso8601String(),
            'dt_deadline' => $this->dt_deadline?->toIso8601String(),
            'dt_completed' => $this->dt_completed?->toIso8601String(),
            'dt_approved' => $this->dt_approved?->toIso8601String(),
            'dt_shipped' => $this->dt_shipped?->toIso8601String(),
            'dt_cancelled' => $this->dt_cancelled?->toIso8601String(),

            // Status
            'status' => $status->value,
            'status_badge' => [
                'label' => ucwords($status->value),
                'color' => $status->badgeColor(),
            ],

            // Relationships
            'assigned_user' => $this->whenLoaded('assignedUser', fn() => [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
            ]),

            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->product_id,
                'name' => $this->product->name,
                'owner' => $this->whenLoaded('product.owner', fn() => [
                    'id' => $this->product->owner?->id,
                    'name' => $this->product->owner?->name,
                ]),
            ]),

            'template' => $this->whenLoaded('template', fn() => [
                'id' => $this->template->template_id,
                'name' => $this->template->template_name,
                'workflow_name' => $this->template->workflow_name,
                'icon' => $this->template->icon,
            ]),

            'approver' => $this->whenLoaded('approver', fn() => [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ]),

            'shipper' => $this->whenLoaded('shipper', fn() => [
                'id' => $this->shipper->id,
                'name' => $this->shipper->name,
            ]),

            'canceller' => $this->whenLoaded('canceller', fn() => [
                'id' => $this->canceller->id,
                'name' => $this->canceller->name,
            ]),

            'cancel_reason' => $this->when($this->dt_cancelled, $this->cancel_reason),

            // Permissions
            'can' => $this->when($request->user(), function () use ($request) {
                $user = $request->user();
                return [
                    'view' => $user->can('view', $this->resource),
                    'update' => $user->can('update', $this->resource),
                    'delete' => $user->can('delete', $this->resource),
                    'approve' => $user->can('approve', $this->resource),
                    'unapprove' => $user->can('unapprove', $this->resource),
                    'ship' => $user->can('ship', $this->resource),
                    'cancel' => $user->can('cancel', $this->resource),
                    'restore' => $user->can('restore', $this->resource),
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
