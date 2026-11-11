<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RepairCategory extends Model
{
    protected $fillable = ['name', 'name_kh', 'slug', 'icon', 'sort_order', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(RepairDevice::class, 'repair_category_device');
    }

    public function services(): HasMany
    {
        return $this->hasMany(RepairService::class, 'repair_category_id');
    }
}
