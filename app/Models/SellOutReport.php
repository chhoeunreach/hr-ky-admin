<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellOutReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'original_invoice_no',
        'user_id',
        'seller_name',
        'branch_name',
        'customer_name',
        'customer_phone',
        'payment_method',
        'total_amount',
        'note',
        'extracted_text',
        'telegram_message_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
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
}
