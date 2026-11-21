<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceNonce extends Model
{
    protected $table = 'app_security_nonces';

    protected $fillable = [
        'device_id', 'session_uuid', 'nonce_sha256', 'expires_at', 'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];
}
