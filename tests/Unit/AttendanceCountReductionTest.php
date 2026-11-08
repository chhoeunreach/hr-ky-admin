<?php

namespace Tests\Unit;

use App\Support\AttendanceCountReduction;
use PHPUnit\Framework\TestCase;

class AttendanceCountReductionTest extends TestCase
{
    public function test_it_builds_late_buckets_at_policy_boundaries(): void
    {
        $breakdown = AttendanceCountReduction::emptyBreakdown();
        foreach ([15, 16, 30, 31] as $minutes) {
            $breakdown = AttendanceCountReduction::addLateMinutes($breakdown, $minutes);
        }

        $this->assertSame(2, $breakdown['late_16_to_30']);
        $this->assertSame(1, $breakdown['late_over_30']);
    }

    public function test_it_deducts_from_the_first_late_and_adds_a_warning_after_three(): void
    {
        $this->assertSame(0.0, AttendanceCountReduction::payment(AttendanceCountReduction::emptyBreakdown(), 0));
        $this->assertSame(0.5, AttendanceCountReduction::payment(['late_16_to_30' => 1], 1));
        $this->assertSame(1.5, AttendanceCountReduction::payment(['late_over_30' => 1], 1));
        $this->assertSame(1.5, AttendanceCountReduction::payment(['late_16_to_30' => 3], 3));
        $this->assertSame(33.0, AttendanceCountReduction::payment(['late_16_to_30' => 3, 'late_over_30' => 1], 4));
    }

    public function test_reason_contains_total_late_count(): void
    {
        $reason = AttendanceCountReduction::reason([
            'late_16_to_30' => 3,
            'late_over_30' => 2,
        ]);

        $this->assertStringContainsString('Total late: 5', $reason);
    }
}
