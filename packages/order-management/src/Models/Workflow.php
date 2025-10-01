<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workflow extends Model
{
    use HasFactory;

    protected $table = 'workflows';
    protected $primaryKey = 'workflow_id';

    protected $fillable = [
        'template_id',
        'workflow_data',
    ];

    protected $casts = [
        'workflow_data' => 'array',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.workflows', 'workflows');
    }

    /**
     * Relationships
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'template_id', 'template_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'workflow_id', 'workflow_id');
    }

    /**
     * Get workflow data or default from template
     */
    public function getEffectiveWorkflowData(): array
    {
        if (is_array($this->workflow_data) && !empty($this->workflow_data)) {
            return $this->workflow_data;
        }

        return $this->template->getWorkflowSettings();
    }
}
