<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairBrand extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'status'];

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
