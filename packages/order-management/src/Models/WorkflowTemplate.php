<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowTemplate extends Model
{
    use HasFactory;

    protected $table = 'workflow_templates';
    protected $primaryKey = 'template_id';

    protected $fillable = [
        'system_id',
        'company_id',
        'store_id',
        'template_name',
        'workflow_name',
        'icon',
        'intro_text',
        'settings',
        'approval_required',
    ];

    protected $casts = [
        'settings' => 'array',
        'approval_required' => 'boolean',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.workflow_templates', 'workflow_templates');
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'company_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'store_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'template_id', 'template_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(TemplateField::class, 'template_id', 'template_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'template_id', 'template_id');
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(Workflow::class, 'template_id', 'template_id');
    }

    /**
     * Check if template belongs to store or company
     */
    public function belongsToStore(): bool
    {
        return !is_null($this->store_id);
    }

    /**
     * Get workflow settings with defaults
     */
    public function getWorkflowSettings(): array
    {
        return $this->settings['workflow'] ?? [];
    }

    /**
     * Check if custom workflows are allowed
     */
    public function allowsCustomWorkflows(): bool
    {
        return $this->settings['workflow']['custom_allowed'] ?? false;
    }
}
