<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HomeHighlightDisplaySetting extends Model
{
    protected $guarded = ['id'];
    protected $attributes = [
        'auto_switch_enabled' => true, 'late_bonus_seconds' => 15, 'highlights_seconds' => 15,
    ];
    protected $casts = [
        'auto_switch_enabled' => 'boolean', 'late_bonus_seconds' => 'integer', 'highlights_seconds' => 'integer',
    ];

    public static function forCompany(?int $companyId): self
    {
        return self::where('company_id', $companyId)->first() ?? new self(['company_id' => $companyId]);
    }

    public function payload(): array
    {
        return $this->only(['auto_switch_enabled', 'late_bonus_seconds', 'highlights_seconds']);
    }
}
