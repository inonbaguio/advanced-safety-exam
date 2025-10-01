<?php

namespace OrderManagement\Repositories;

use OrderManagement\Models\Workflow;
use OrderManagement\Models\WorkflowTemplate;
use Illuminate\Database\Eloquent\Collection;

class WorkflowRepository
{
    /**
     * Find workflow by ID
     */
    public function find(int $id): ?Workflow
    {
        return Workflow::with('template')->find($id);
    }

    /**
     * Get workflows by template
     */
    public function getByTemplate(int $templateId): Collection
    {
        return Workflow::where('template_id', $templateId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new workflow
     */
    public function create(array $data): Workflow
    {
        return Workflow::create($data);
    }

    /**
     * Update a workflow
     */
    public function update(Workflow $workflow, array $data): bool
    {
        return $workflow->update($data);
    }

    /**
     * Find template by ID
     */
    public function findTemplate(int $id): ?WorkflowTemplate
    {
        return WorkflowTemplate::with(['company', 'store', 'fields'])
            ->find($id);
    }

    /**
     * Get all templates
     */
    public function getAllTemplates(): Collection
    {
        return WorkflowTemplate::with(['company', 'store'])
            ->orderBy('template_name')
            ->get();
    }

    /**
     * Get templates by company
     */
    public function getTemplatesByCompany(int $companyId): Collection
    {
        return WorkflowTemplate::where('company_id', $companyId)
            ->orderBy('template_name')
            ->get();
    }

    /**
     * Get templates by store
     */
    public function getTemplatesByStore(int $storeId): Collection
    {
        return WorkflowTemplate::where('store_id', $storeId)
            ->orderBy('template_name')
            ->get();
    }
}
