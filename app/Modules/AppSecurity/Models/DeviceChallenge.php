<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceChallenge extends Model
{
    protected $table = 'app_device_challenges';

    protected $fillable = [
        'uuid', 'user_id', 'device_id', 'purpose', 'challenge_sha256',
        'expires_at', 'used_at', 'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
    ];
}
