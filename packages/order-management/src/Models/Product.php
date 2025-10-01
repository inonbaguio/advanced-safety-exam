<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'owner_id',
        'template_id',
        'name',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.products', 'products');
    }

    /**
     * Relationships
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'template_id', 'template_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'product_id', 'product_id');
    }

    /**
     * Check if product has an owner
     */
    public function hasOwner(): bool
    {
        return !is_null($this->owner_id);
    }
}
