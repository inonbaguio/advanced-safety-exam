<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class OrderPermission extends Model
{
    use HasFactory;

    protected $table = 'order_permissions';

    protected $fillable = [
        'order_id',
        'user_id',
        'module',
        'permission_type',
        'can_approve',
        'can_edit',
        'can_ship',
        'can_cancel',
    ];

    protected $casts = [
        'can_approve' => 'boolean',
        'can_edit' => 'boolean',
        'can_ship' => 'boolean',
        'can_cancel' => 'boolean',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.order_permissions', 'order_permissions');
    }

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeManager($query)
    {
        return $query->where('permission_type', 'manager');
    }

    public function scopeCanApprove($query)
    {
        return $query->where('can_approve', true);
    }
}
