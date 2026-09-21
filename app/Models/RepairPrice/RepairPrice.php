<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class RepairPrice extends Model
{
    protected $fillable = [
        'repair_device_id',
        'repair_service_id',
        'part_name',
        'part_type',
        'part_cost',
        'service_fee',
        'selling_price',
        'warranty_days',
        'estimated_minutes',
        'note',
        'availability_status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'part_cost' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $price) {
            $price->created_by = Auth::guard('admin')->id() ?? Auth::id();
            $price->updated_by = Auth::guard('admin')->id() ?? Auth::id();
        });

        static::updating(function (self $price) {
            $price->updated_by = Auth::guard('admin')->id() ?? Auth::id();
        });
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(RepairDevice::class, 'repair_device_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(RepairService::class, 'repair_service_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RepairPriceHistory::class, 'repair_price_id');
    }
}
