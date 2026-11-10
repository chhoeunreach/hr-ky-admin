<?php

namespace App\Services\SellOut;

/** Existing invoice rates, shared by the mobile reporting SQL and model. */
final class ReportingRules
{
    public const ALIASES = [
        'sell' => ['sell', 'sale', 'លក់', ''],
        'material' => ['material', 'សម្ភារ'],
        'iron' => ['iron', 'scots', 'អ៊ុត'],
        'repair' => ['repair', 'ជួសជុល'],
        'buy_in' => ['buy in', 'buy_in', 'buy', 'ទិញចូល', 'ទិញ'],
        'icloud_cus' => ['icloud cus', 'icloud_cus'],
    ];
    public const RATES = ['sell' => 0.25, 'material' => 0.25, 'iron' => 0.20, 'repair' => 0.20];
    public const PART_TIME_THRESHOLD = 50;
    public const FULL_TIME_THRESHOLD = 100;

    public static function canonical(string $value): string
    {
        $value = mb_strtolower(trim(preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value)));
        foreach (self::ALIASES as $type => $aliases) {
            if (in_array($value, $aliases, true)) return $type;
        }
        return $value;
    }

    public static function threshold(string $category): int
    {
        return mb_strtolower(trim(preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $category))) === 'part_timer' ? self::PART_TIME_THRESHOLD : self::FULL_TIME_THRESHOLD;
    }

    public static function calculate(string $type, int $qty, float $amount, string $phone, string $category, ?int $periodQty = null, bool $conditions = true): float
    {
        $type = self::canonical($type);
        if (!isset(self::RATES[$type])) return 0;
        if ($conditions && (mb_strlen(trim($phone)) <= 6 || ($type === 'material' && $amount < 10) || ($type === 'sell' && ($periodQty ?? $qty) < self::threshold($category)))) return 0;
        return ($type === 'sell' ? $qty : 1) * self::RATES[$type];
    }

    public static function typeSql(string $column): string
    {
        // All SQL fragments/aliases are internal constants, never request input.
        $clean = self::normalizedSql($column);
        $sql = 'CASE';
        foreach (self::ALIASES as $type => $aliases) {
            $quoted = implode(',', array_map(fn ($a) => "'".str_replace("'", "''", $a)."'", $aliases));
            $sql .= " WHEN $clean IN ($quoted) THEN '$type'";
        }
        return "$sql ELSE 'other' END";
    }

    public static function normalizedSql(string $column): string
    {
        $clean = "COALESCE($column, '')";
        foreach (["\u{200B}", "\u{200C}", "\u{200D}", "\u{FEFF}"] as $char) $clean = "REPLACE($clean, '$char', '')";
        return "LOWER(TRIM($clean))";
    }

    public static function eligibleSql(bool $conditions): string
    {
        $supported = "d.type IN ('sell','material','iron','repair')";
        if (!$conditions) return $supported;
        $part = self::PART_TIME_THRESHOLD;
        $full = self::FULL_TIME_THRESHOLD;
        return "$supported AND d.phone_valid = 1 AND (d.type <> 'material' OR d.total_amount >= 10) AND (d.type <> 'sell' OR COALESCE(p.period_qty,0) >= CASE WHEN d.category = 'part_timer' THEN $part ELSE $full END)";
    }

    public static function rateSql(): string
    {
        $sql = 'CASE d.type';
        foreach (self::RATES as $type => $rate) $sql .= " WHEN '$type' THEN $rate";
        return "$sql ELSE 0 END";
    }
}
