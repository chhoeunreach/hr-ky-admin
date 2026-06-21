<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellOutReportLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sell_out_report_id',
        'product_id',
        'variation_id',
        'product_name',
        'sku',
        'identifier_type',
        'primary_identifier',
        'imei',
        'imei2',
        'serial_number',
        'model_number',
        'color',
        'storage',
        'qty',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SellOutReport::class, 'sell_out_report_id');
    }
}
