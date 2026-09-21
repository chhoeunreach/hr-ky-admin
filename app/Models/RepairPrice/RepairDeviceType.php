<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairDeviceType extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function series(): HasMany
    {
        return $this->hasMany(RepairDeviceSeries::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(RepairDevice::class);
    }
}
