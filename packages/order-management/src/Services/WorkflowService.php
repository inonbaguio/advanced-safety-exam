<?php

namespace OrderManagement\Services;

use OrderManagement\Models\Workflow;
use OrderManagement\Models\WorkflowTemplate;
use OrderManagement\Models\TemplateField;
use OrderManagement\Repositories\WorkflowRepository;

class WorkflowService
{
    public function __construct(
        protected WorkflowRepository $repository
    ) {}

    /**
     * Get effective workflow data for an order
     * If custom workflow exists, use it; otherwise use template settings
     */
    public function getEffectiveWorkflowData(?Workflow $workflow, WorkflowTemplate $template): array
    {
        if ($workflow) {
            return $workflow->getEffectiveWorkflowData();
        }

        return $template->getWorkflowSettings();
    }

    /**
     * Create a custom workflow for a template
     */
    public function createCustomWorkflow(WorkflowTemplate $template, array $workflowData): ?Workflow
    {
        // Check if custom workflows are allowed
        if (!$template->allowsCustomWorkflows()) {
            return null;
        }

        return $this->repository->create([
            'template_id' => $template->template_id,
            'workflow_data' => $workflowData,
        ]);
    }

    /**
     * Get template fields for a specific purpose
     */
    public function getTemplateFields(WorkflowTemplate $template): array
    {
        return [
            'completion_date' => TemplateField::findCompletionDateField($template->template_id),
            'priority' => TemplateField::findPriorityField($template->template_id),
        ];
    }

    /**
     * Check if workflow is recurring
     */
    public function isRecurring(?Workflow $workflow): bool
    {
        if (!$workflow) {
            return false;
        }

        $data = $workflow->getEffectiveWorkflowData();
        return !empty($data['frequency']) && $data['frequency'] !== 'one-time';
    }

    /**
     * Get workflow frequency description
     */
    public function getFrequencyDescription(?Workflow $workflow, WorkflowTemplate $template): string
    {
        if (!$workflow) {
            return 'One-Time Order';
        }

        $data = $this->getEffectiveWorkflowData($workflow, $template);

        if (empty($data['frequency'])) {
            return 'One-Time Order';
        }

        // Build frequency description based on workflow data
        return $this->formatFrequency($data);
    }

    /**
     * Format workflow frequency into human-readable text
     */
    protected function formatFrequency(array $workflowData): string
    {
        $frequency = $workflowData['frequency'] ?? 'one-time';

        return match($frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'biweekly' => 'Bi-Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            'one-time' => 'One-Time Order',
            default => ucfirst($frequency),
        };
    }

    /**
     * Get approval style for template (from store or company)
     */
    public function getApprovalStyle(WorkflowTemplate $template): string
    {
        if ($template->belongsToStore()) {
            return $template->store->getEffectiveApprovalStyle();
        }

        return $template->company->approval_style ?? 'Per User';
    }
}
