<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores';
    protected $primaryKey = 'store_id';

    protected $fillable = [
        'company_id',
        'name',
        'approval_style',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.stores', 'stores');
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'store_id', 'store_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(WorkflowTemplate::class, 'store_id', 'store_id');
    }

    /**
     * Get approval style (can inherit from company)
     */
    public function getEffectiveApprovalStyle(): string
    {
        return $this->approval_style ?? $this->company->approval_style ?? 'Per User';
    }
}
