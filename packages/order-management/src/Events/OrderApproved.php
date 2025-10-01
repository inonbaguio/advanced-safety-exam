<?php

namespace OrderManagement\Events;

use OrderManagement\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $approver
    ) {}
}
