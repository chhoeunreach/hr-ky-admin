<?php

namespace App\Models\RepairPrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepairService extends Model
{
    protected $fillable = ['repair_category_id', 'name', 'name_kh', 'description', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RepairCategory::class, 'repair_category_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RepairPrice::class, 'repair_service_id');
    }
}
