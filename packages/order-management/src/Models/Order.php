<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    protected $fillable = [
        'customer_id',
        'store_id',
        'product_id',
        'workflow_id',
        'template_id',
        'title',
        'notes',
        'assigned_to',
        'created_by',
        'dt_created',
        'dt_required',
        'dt_deadline',
        'dt_completed',
        'dt_approved',
        'dt_shipped',
        'dt_cancelled',
        'completed_by',
        'approved_by',
        'shipped_by',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'dt_created' => 'datetime',
        'dt_required' => 'datetime',
        'dt_deadline' => 'datetime',
        'dt_completed' => 'datetime',
        'dt_approved' => 'datetime',
        'dt_shipped' => 'datetime',
        'dt_cancelled' => 'datetime',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.orders', 'orders');
    }

    /**
     * Relationships
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id', 'workflow_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'template_id', 'template_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'store_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(OrderPermission::class, 'order_id', 'order_id');
    }

    /**
     * Scopes
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopePending($query)
    {
        return $query->whereNull('dt_approved')
            ->whereNull('dt_cancelled')
            ->whereNull('dt_shipped');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('dt_approved')
            ->whereNull('dt_shipped')
            ->whereNull('dt_cancelled');
    }

    public function scopeShipped($query)
    {
        return $query->whereNotNull('dt_shipped');
    }

    public function scopeCancelled($query)
    {
        return $query->whereNotNull('dt_cancelled');
    }

    /**
     * Check if order belongs to user
     */
    public function belongsToUser(int $userId): bool
    {
        return $this->assigned_to === $userId;
    }

    /**
     * Check if order is past deadline
     */
    public function isPastDeadline(): bool
    {
        return $this->dt_deadline && now()->gt($this->dt_deadline);
    }

    /**
     * Check if order is past required date
     */
    public function isPastRequiredDate(): bool
    {
        return $this->dt_required && now()->gt($this->dt_required);
    }

    /**
     * Check if required date is approaching (within warning threshold)
     */
    public function isRequiredDateApproaching(): bool
    {
        if (!$this->dt_required) {
            return false;
        }

        $warningThreshold = config('order-management.settings.warning_threshold_days', 3);
        $warningDate = now()->addDays($warningThreshold);

        return $this->dt_required->lte($warningDate) && !$this->isPastRequiredDate();
    }
}
