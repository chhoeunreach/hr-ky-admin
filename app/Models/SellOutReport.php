<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellOutReport extends Model
{
    use HasFactory;

    public const DEFAULT_SERVICE_TYPE = 'លក់';

    protected $fillable = [
        'invoice_no',
        'original_invoice_no',
        'user_id',
        'seller_name',
        'branch_name',
        'customer_name',
        'customer_phone',
        'service_type',
        'payment_method',
        'total_amount',
        'commission',
        'note',
        'extracted_text',
        'telegram_message_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SellOutReportLine::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SellOutReportPhoto::class);
    }

    public function getDisplayServiceTypeAttribute(): string
    {
        return trim((string) $this->service_type) !== ''
            ? $this->service_type
            : self::DEFAULT_SERVICE_TYPE;
    }

    public function calculatedCommission(): float
    {
        $serviceType = $this->normalizeServiceType($this->display_service_type);

        if (! $this->isCommissionableServiceType($serviceType) || mb_strlen(trim((string) $this->customer_phone)) <= 6) {
            return 0;
        }

        $lines = $this->relationLoaded('lines') ? $this->lines : $this->lines()->get();

        if ($this->isSellServiceType($serviceType)) {
            $totalQty = $lines->sum(fn (SellOutReportLine $line): int => (int) $line->qty);

            return $totalQty >= $this->sellQtyThreshold() ? $totalQty * 0.25 : 0;
        }

        if ($this->isIronServiceType($serviceType) || $this->isRepairServiceType($serviceType)) {
            return 0.20;
        }

        if ($this->isMaterialServiceType($serviceType)) {
            return (float) $this->total_amount >= 10 ? 0.25 : 0;
        }

        return 0;
    }

    private function isCommissionableServiceType(string $serviceType): bool
    {
        return in_array($this->normalizeServiceType($serviceType), ['Sell', 'Sale', 'លក់', 'Material', 'សម្ភារ', 'Iron', 'Scots', 'អ៊ុត', 'Repair', 'ជួសជុល'], true);
    }

    private function isSellServiceType(string $serviceType): bool
    {
        return in_array($this->normalizeServiceType($serviceType), ['Sell', 'Sale', 'លក់'], true);
    }

    private function isIronServiceType(string $serviceType): bool
    {
        return in_array($this->normalizeServiceType($serviceType), ['Iron', 'Scots', 'អ៊ុត'], true);
    }

    private function isMaterialServiceType(string $serviceType): bool
    {
        return in_array($this->normalizeServiceType($serviceType), ['Material', 'សម្ភារ'], true);
    }

    private function isRepairServiceType(string $serviceType): bool
    {
        return in_array($this->normalizeServiceType($serviceType), ['Repair', 'ជួសជុល'], true);
    }

    private function sellQtyThreshold(): int
    {
        $user = $this->relationLoaded('user') ? $this->user : $this->user()->with('officeTime')->first();
        $category = $this->normalizeServiceType((string) ($user?->officeTime?->category ?? ''));

        return $category === 'part_timer' ? 50 : 100;
    }

    private function normalizeServiceType(string $serviceType): string
    {
        return trim((string) preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $serviceType));
    }
}
