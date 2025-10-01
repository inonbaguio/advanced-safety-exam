<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';
    protected $primaryKey = 'company_id';

    protected $fillable = [
        'name',
        'approval_style',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.companies', 'companies');
    }

    /**
     * Relationships
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'company_id', 'company_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(WorkflowTemplate::class, 'company_id', 'company_id');
    }
}
