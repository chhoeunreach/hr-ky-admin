<?php

namespace App\Support;

class EmployeeAvatar
{
    public static function borderColor(int $employeeId): string
    {
        $colors = ['var(--bs-primary, #5546ed)', '#5546ed', '#10b981', '#e11d48', '#0284c7'];
        return $colors[$employeeId % count($colors)];
    }
}
