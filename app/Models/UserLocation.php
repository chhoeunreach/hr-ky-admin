<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_key',
        'device_type',
        'latitude',
        'longitude',
        'accuracy',
        'battery_level',
        'device_name',
        'app_name',
        'app_version',
        'app_build',
        'device_model',
        'os_version',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'battery_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
