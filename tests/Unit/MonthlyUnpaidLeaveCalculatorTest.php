<?php

namespace Tests\Unit;

use App\Services\Payroll\MonthlyUnpaidLeaveCalculator;
use PHPUnit\Framework\TestCase;

class MonthlyUnpaidLeaveCalculatorTest extends TestCase
{
    public function test_unpaid_leave_uses_fixed_30_days_and_rounds_only_the_result(): void
    {
        $this->assertSame(20.0, MonthlyUnpaidLeaveCalculator::amount(300, 2));
        $this->assertSame(5.0, MonthlyUnpaidLeaveCalculator::amount(300, .5));
        $this->assertSame(16.67, MonthlyUnpaidLeaveCalculator::amount(250, 2));
        $this->assertSame(0.0, MonthlyUnpaidLeaveCalculator::amount(300, 0));
        $this->assertSame(0.0, MonthlyUnpaidLeaveCalculator::amount(0, 2));
    }
}
