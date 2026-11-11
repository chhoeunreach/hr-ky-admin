<?php

namespace App\Services\SellOut;

/** Existing invoice rates, shared by the mobile reporting SQL and model. */
final class ReportingRules
{
    public const ALIASES = [
        'sell' => ['sell', 'sale', 'លក់', ''],
        'sellsim' => ['sellsim', 'sell sim', 'sell_sim', 'sell-sim', 'sim sale', 'sim_sale', 'លក់ស៊ីម'],
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
        $configuration = app(CommissionSettingsService::class)->configuration();
        $rule = $configuration['rules'][$type] ?? null;
        if (!$configuration['enabled'] || !$rule || !$rule['enabled']) return 0;
        $threshold = self::category($category) === 'part_timer' ? $rule['part_time_quantity'] : $rule['full_time_quantity'];
        if ($conditions && (($rule['require_phone'] && mb_strlen(trim($phone)) <= 6)
            || $amount < $rule['minimum_amount'] || ($periodQty ?? $qty) < $threshold)) return 0;
        return round(($rule['basis'] === 'item' ? $qty : 1) * $rule['rate'], 2);
    }

    private static function category(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value)));
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

    public static function enabledSql(string $alias = 'd'): string
    {
        $c = app(CommissionSettingsService::class)->configuration();
        $types = $c['enabled'] ? array_keys(array_filter($c['rules'],fn ($r) => $r['enabled'])) : [];
        return $types ? "$alias.type IN ('".implode("','",$types)."')" : '1 = 0';
    }

    public static function periodQuantitySql(): string
    {
        $parts = [];
        foreach (app(CommissionSettingsService::class)->configuration()['rules'] as $type=>$rule) {
            $parts[] = "WHEN type = '$type' THEN ".($rule['require_phone'] ? 'CASE WHEN phone_valid = 1 THEN total_qty ELSE 0 END' : 'total_qty');
        }
        return 'CASE '.implode(' ',$parts).' ELSE 0 END';
    }

    public static function eligibleSql(bool $conditions): string
    {
        if (!$conditions) return self::enabledSql();
        $parts = [];
        foreach (app(CommissionSettingsService::class)->configuration()['rules'] as $type=>$rule) {
            $phone = $rule['require_phone'] ? 'd.phone_valid = 1' : '1 = 1';
            $amount = (float)$rule['minimum_amount'];
            $part = (int)$rule['part_time_quantity']; $full = (int)$rule['full_time_quantity'];
            $parts[] = "(d.type = '$type' AND $phone AND d.total_amount >= $amount AND COALESCE(p.period_qty,0) >= CASE WHEN d.category = 'part_timer' THEN $part ELSE $full END)";
        }
        return self::enabledSql().' AND ('.implode(' OR ',$parts).')';
    }

    public static function rateSql(): string
    {
        $sql = 'CASE d.type';
        foreach (app(CommissionSettingsService::class)->configuration()['rules'] as $type=>$rule) {
            $rate = (float)$rule['rate'];
            $units = $rule['basis'] === 'item' ? 'd.total_qty' : '1';
            $sql .= " WHEN '$type' THEN $rate * $units";
        }
        return "$sql ELSE 0 END";
    }
}
