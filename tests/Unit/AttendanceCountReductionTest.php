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

    public function test_it_requires_four_effective_lates_before_deducting(): void
    {
        $breakdown = ['late_16_to_30' => 3, 'late_over_30' => 1];

        $this->assertSame(0.0, AttendanceCountReduction::payment($breakdown, 3));
        $this->assertSame(33.0, AttendanceCountReduction::payment($breakdown, 4));
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
