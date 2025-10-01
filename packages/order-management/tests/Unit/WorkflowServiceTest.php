<?php

namespace OrderManagement\Tests\Unit;

use OrderManagement\Models\Workflow;
use OrderManagement\Models\WorkflowTemplate;
use OrderManagement\Services\WorkflowService;
use OrderManagement\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkflowService::class);
    }

    /** @test */
    public function it_returns_workflow_data_when_custom_workflow_exists()
    {
        $template = WorkflowTemplate::factory()->create([
            'settings' => [
                'workflow' => ['frequency' => 'weekly'],
            ],
        ]);

        $workflow = Workflow::factory()->create([
            'template_id' => $template->template_id,
            'workflow_data' => ['frequency' => 'daily', 'custom' => true],
        ]);

        $data = $this->service->getEffectiveWorkflowData($workflow, $template);

        $this->assertEquals('daily', $data['frequency']);
    }

    /** @test */
    public function it_returns_template_settings_when_no_custom_workflow()
    {
        $template = WorkflowTemplate::factory()->create([
            'settings' => [
                'workflow' => ['frequency' => 'monthly'],
            ],
        ]);

        $data = $this->service->getEffectiveWorkflowData(null, $template);

        $this->assertEquals('monthly', $data['frequency']);
    }

    /** @test */
    public function it_creates_custom_workflow_when_allowed()
    {
        $template = WorkflowTemplate::factory()->create([
            'settings' => [
                'workflow' => [
                    'custom_allowed' => true,
                ],
            ],
        ]);

        $workflow = $this->service->createCustomWorkflow($template, [
            'frequency' => 'quarterly',
        ]);

        $this->assertNotNull($workflow);
        $this->assertEquals('quarterly', $workflow->workflow_data['frequency']);
    }

    /** @test */
    public function it_returns_null_when_custom_workflow_not_allowed()
    {
        $template = WorkflowTemplate::factory()->create([
            'settings' => [
                'workflow' => [
                    'custom_allowed' => false,
                ],
            ],
        ]);

        $workflow = $this->service->createCustomWorkflow($template, [
            'frequency' => 'quarterly',
        ]);

        $this->assertNull($workflow);
    }

    /** @test */
    public function it_checks_if_workflow_is_recurring()
    {
        $recurringWorkflow = Workflow::factory()->create([
            'workflow_data' => ['frequency' => 'weekly'],
        ]);

        $oneTimeWorkflow = Workflow::factory()->create([
            'workflow_data' => ['frequency' => 'one-time'],
        ]);

        $this->assertTrue($this->service->isRecurring($recurringWorkflow));
        $this->assertFalse($this->service->isRecurring($oneTimeWorkflow));
        $this->assertFalse($this->service->isRecurring(null));
    }

    /** @test */
    public function it_formats_frequency_description()
    {
        $template = WorkflowTemplate::factory()->create();
        $workflow = Workflow::factory()->daily()->create(['template_id' => $template->template_id]);

        $description = $this->service->getFrequencyDescription($workflow, $template);

        $this->assertEquals('Daily', $description);
    }

    /** @test */
    public function it_returns_one_time_order_when_no_workflow()
    {
        $template = WorkflowTemplate::factory()->create();

        $description = $this->service->getFrequencyDescription(null, $template);

        $this->assertEquals('One-Time Order', $description);
    }
}
