<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairDevice extends Model
{
    protected $fillable = [
        'repair_brand_id',
        'repair_device_type_id',
        'repair_device_series_id',
        'name',
        'model_number',
        'image',
        'status',
    ];

    protected $casts = ['status' => 'boolean'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(RepairBrand::class, 'repair_brand_id');
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(RepairDeviceType::class, 'repair_device_type_id');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(RepairDeviceSeries::class, 'repair_device_series_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RepairPrice::class, 'repair_device_id');
    }
}
