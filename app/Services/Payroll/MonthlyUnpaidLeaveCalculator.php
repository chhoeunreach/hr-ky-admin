<?php

namespace App\Services\Payroll;

class MonthlyUnpaidLeaveCalculator
{
    /** Monthly unpaid leave uses a fixed 30-day divisor, including February and 31-day months. */
    public static function amount(float $monthlySalary, float $unpaidDays): float
    {
        return round(max(0, $monthlySalary) / 30 * max(0, $unpaidDays), 2);
    }
}
