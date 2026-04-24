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
        'latitude',
        'longitude',
        'accuracy',
        'battery_level',
        'device_name',
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
