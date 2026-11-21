<?php

namespace App\Modules\DailyRecords;

use Carbon\CarbonImmutable;

final class RecordTime
{
    public const ZONE = 'Asia/Phnom_Penh';

    public static function now(): string
    {
        return CarbonImmutable::now('UTC')->toDateTimeString();
    }

    public static function utc(string $value): string
    {
        return CarbonImmutable::parse($value, self::ZONE)->utc()->toDateTimeString();
    }

    public static function day(string $utc): string
    {
        return CarbonImmutable::parse($utc, 'UTC')->setTimezone(self::ZONE)->toDateString();
    }

    public static function display(?string $utc): string
    {
        return $utc ? CarbonImmutable::parse($utc, 'UTC')->setTimezone(self::ZONE)->format('Y-m-d H:i') : '—';
    }
}
