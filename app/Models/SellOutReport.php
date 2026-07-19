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
}
