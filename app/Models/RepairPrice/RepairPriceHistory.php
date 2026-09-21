<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairPriceHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'repair_price_id',
        'old_part_cost',
        'new_part_cost',
        'old_service_fee',
        'new_service_fee',
        'old_selling_price',
        'new_selling_price',
        'changed_by',
        'created_at',
    ];

    public function price(): BelongsTo
    {
        return $this->belongsTo(RepairPrice::class, 'repair_price_id');
    }
}
