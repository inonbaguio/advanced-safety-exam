<?php

namespace OrderManagement\Events;

use OrderManagement\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $canceller,
        public ?string $reason = null
    ) {}
}
