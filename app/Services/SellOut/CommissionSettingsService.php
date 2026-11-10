<?php

namespace App\Services\SellOut;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** One global policy; request-scoped so list resources share one database read. */
final class CommissionSettingsService
{
    private ?array $configuration = null;

    public static function defaults(): array
    {
        $rules = [];
        foreach (['sell','material','iron','repair','buy_in','icloud_cus'] as $type) {
            $rules[$type] = [
                'enabled'=>in_array($type,['sell','material','iron','repair'],true),
                'rate'=>ReportingRules::RATES[$type] ?? 0,
                'basis'=>$type === 'sell' ? 'item' : 'invoice', 'require_phone'=>true,
                'minimum_amount'=>$type === 'material' ? 10 : 0,
                'part_time_quantity'=>$type === 'sell' ? ReportingRules::PART_TIME_THRESHOLD : 0,
                'full_time_quantity'=>$type === 'sell' ? ReportingRules::FULL_TIME_THRESHOLD : 0,
            ];
        }
        return ['enabled'=>true,'visible'=>true,'calculator_enabled'=>true,
            'allow_mode_switch'=>true,'default_mode'=>'with_conditions','rules'=>$rules,'roles'=>[]];
    }

    public function configuration(): array
    {
        if ($this->configuration === null) {
            $installed = Schema::hasTable('commission_settings');
            $saved = $installed ? DB::table('commission_settings')->where('id',1)->value('configuration') : null;
            abort_if($installed && !$saved,503,'Commission configuration is unavailable.');
            $this->configuration = array_replace_recursive(self::defaults(), $saved ? json_decode($saved,true,512,JSON_THROW_ON_ERROR) : []);
        }
        return $this->configuration;
    }

    public function refresh(): void { $this->configuration = null; }

    public function policy($user): array
    {
        $c = $this->configuration();
        $role = $c['roles'][(string)($user?->role_id ?? '')] ?? [];
        $visible = $c['visible'] && ($role['view'] ?? true);
        $calculate = $visible && $c['calculator_enabled'] && ($role['calculate'] ?? true);
        return ['visible'=>(bool)$visible,'can_calculate'=>(bool)$calculate,
            'can_change_mode'=>(bool)($calculate && $c['allow_mode_switch'] && ($role['change_mode'] ?? true)),
            'enabled'=>(bool)$c['enabled'], 'default_mode'=>$c['default_mode'],
            'rules'=>$visible ? $c['rules'] : (object)[]];
    }

    public function mode($user, ?string $requested): string
    {
        $policy = $this->policy($user);
        return $policy['can_change_mode'] && $requested ? $requested : $policy['default_mode'];
    }
}
