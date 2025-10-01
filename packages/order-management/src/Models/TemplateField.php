<?php

namespace OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateField extends Model
{
    use HasFactory;

    protected $table = 'template_fields';
    protected $primaryKey = 'field_id';

    protected $fillable = [
        'template_id',
        'field_type',
        'field_name',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'settings' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the table name from config
     */
    public function getTable(): string
    {
        return config('order-management.tables.template_fields', 'template_fields');
    }

    /**
     * Relationships
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'template_id', 'template_id');
    }

    /**
     * Scopes
     */
    public function scopeOrderedBySort($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('field_type', $type);
    }

    /**
     * Check if field has specific column setting
     */
    public function hasColumnSetting(string $column): bool
    {
        return isset($this->settings['column']) && $this->settings['column'] === $column;
    }

    /**
     * Find completion date field for template
     */
    public static function findCompletionDateField(int $templateId): ?self
    {
        return self::where('template_id', $templateId)
            ->whereJsonContains('settings->column', 'dt_completed')
            ->first();
    }

    /**
     * Find priority field for template
     */
    public static function findPriorityField(int $templateId): ?self
    {
        return self::where('template_id', $templateId)
            ->where('field_type', 'Priority Level')
            ->first();
    }
}
