<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairDeviceSeries extends Model
{
    protected $table = 'repair_device_series';

    protected $fillable = ['repair_brand_id', 'repair_device_type_id', 'name', 'slug', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(RepairBrand::class, 'repair_brand_id');
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(RepairDeviceType::class, 'repair_device_type_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(RepairDevice::class, 'repair_device_series_id');
    }
}
