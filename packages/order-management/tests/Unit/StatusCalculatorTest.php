<?php

namespace OrderManagement\Tests\Unit;

use OrderManagement\Models\Order;
use OrderManagement\Services\StatusCalculator;
use OrderManagement\Enums\OrderStatus;
use OrderManagement\Tests\TestCase;

class StatusCalculatorTest extends TestCase
{
    protected StatusCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new StatusCalculator();
    }

    /** @test */
    public function it_returns_cancelled_when_both_cancelled_and_shipped()
    {
        $order = new Order([
            'dt_cancelled' => now(),
            'dt_shipped' => now(),
        ]);

        $status = $this->calculator->calculate($order);

        $this->assertEquals(OrderStatus::Cancelled, $status);
    }

    /** @test */
    public function it_returns_shipped_when_shipped()
    {
        $order = new Order([
            'dt_shipped' => now(),
            'dt_cancelled' => null,
        ]);

        $status = $this->calculator->calculate($order);

        $this->assertEquals(OrderStatus::Shipped, $status);
    }

    /** @test */
    public function it_returns_approved_when_approved_but_not_shipped()
    {
        $order = new Order([
            'dt_approved' => now(),
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);

        $status = $this->calculator->calculate($order);

        $this->assertEquals(OrderStatus::Approved, $status);
    }

    /** @test */
    public function it_returns_overdue_when_past_deadline()
    {
        $order = new Order([
            'dt_deadline' => now()->subDays(1),
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);

        $status = $this->calculator->calculate($order);

        $this->assertEquals(OrderStatus::Overdue, $status);
    }

    /** @test */
    public function it_returns_late_when_past_required_date()
    {
        $order = new Order([
            'dt_required' => now()->subDays(1),
            'dt_deadline' => null,
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);

        $status = $this->calculator->calculate($order);

        $this->assertEquals(OrderStatus::Late, $status);
    }

    /** @test */
    public function it_returns_pending_as_default()
    {
        $order = new Order([
            'dt_required' => now()->addDays(7),
            'dt_deadline' => null,
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);

        $status = $this->calculator->calculate($order);

        $this->assertEquals(OrderStatus::Pending, $status);
    }

    /** @test */
    public function it_returns_status_badge_information()
    {
        $order = new Order([
            'dt_approved' => now(),
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);

        $badge = $this->calculator->getStatusBadge($order);

        $this->assertEquals('Approved', $badge['status']);
        $this->assertEquals('info', $badge['color']);
        $this->assertEquals('Approved', $badge['label']);
    }

    /** @test */
    public function it_checks_if_order_can_be_edited()
    {
        $editableOrder = new Order([
            'dt_approved' => null,
            'dt_shipped' => null,
            'dt_cancelled' => null,
        ]);

        $shippedOrder = new Order([
            'dt_shipped' => now(),
        ]);

        $this->assertTrue($this->calculator->canEdit($editableOrder));
        $this->assertFalse($this->calculator->canEdit($shippedOrder));
    }

    /** @test */
    public function it_checks_if_status_is_terminal()
    {
        $shippedOrder = new Order(['dt_shipped' => now()]);
        $cancelledOrder = new Order(['dt_cancelled' => now()]);
        $pendingOrder = new Order([]);

        $this->assertTrue($this->calculator->isTerminal($shippedOrder));
        $this->assertTrue($this->calculator->isTerminal($cancelledOrder));
        $this->assertFalse($this->calculator->isTerminal($pendingOrder));
    }
}
