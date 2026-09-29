<?php

namespace App\Support;

final class AttendanceCountReduction
{
    public const MIN_LATE_MINUTES = 16;
    public const EFFECTIVE_LATE_THRESHOLD = 4;
    public const STANDARD_RATE = 0.50;
    public const OVER_30_RATE = 1.50;
    public const FINAL_WARNING_RATE = 30.00;

    public static function emptyBreakdown(): array
    {
        return [
            'late_16_to_30' => 0,
            'late_over_30' => 0,
        ];
    }

    public static function addLateMinutes(array $breakdown, ?int $lateMinutes): array
    {
        if ($lateMinutes === null || $lateMinutes < self::MIN_LATE_MINUTES) {
            return $breakdown;
        }

        $key = $lateMinutes <= 30 ? 'late_16_to_30' : 'late_over_30';
        $breakdown[$key] = ((int) ($breakdown[$key] ?? 0)) + 1;

        return $breakdown;
    }

    public static function lateCount(array $breakdown): int
    {
        return (int) ($breakdown['late_16_to_30'] ?? 0)
            + (int) ($breakdown['late_over_30'] ?? 0);
    }

    public static function qualifies(int $effectiveLateCount): bool
    {
        return $effectiveLateCount >= self::EFFECTIVE_LATE_THRESHOLD;
    }

    public static function payment(array $breakdown, int $effectiveLateCount): float
    {
        if (!self::qualifies($effectiveLateCount)) {
            return 0.0;
        }

        $late16To30 = (int) ($breakdown['late_16_to_30'] ?? 0);
        $lateOver30 = (int) ($breakdown['late_over_30'] ?? 0);
        $warningPayment = self::lateCount($breakdown) > 3 ? self::FINAL_WARNING_RATE : 0;

        return ($late16To30 * self::STANDARD_RATE)
            + ($lateOver30 * self::OVER_30_RATE)
            + $warningPayment;
    }

    public static function reason(array $breakdown): string
    {
        $parts = [];
        $late16To30 = (int) ($breakdown['late_16_to_30'] ?? 0);
        $lateOver30 = (int) ($breakdown['late_over_30'] ?? 0);
        $lateCount = self::lateCount($breakdown);

        if ($late16To30 > 0) {
            $parts[] = 'Late 16-30m x ' . $late16To30 . ' at $0.50';
        }
        if ($lateOver30 > 0) {
            $parts[] = 'Late over 30m x ' . $lateOver30 . ' at $1.50';
        }
        $parts[] = 'Total late: ' . $lateCount;
        if ($lateCount > 3) {
            $parts[] = 'Over 3 late times: $30 final warning';
        }

        return 'Count option - ' . implode('; ', $parts);
    }
}
