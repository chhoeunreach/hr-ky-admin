<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecuritySession extends Model
{
    protected $table = 'app_security_sessions';

    protected $fillable = [
        'uuid', 'device_id', 'user_id', 'session_token_sha256',
        'refresh_token_sha256', 'expires_at', 'refresh_expires_at',
        'last_used_at', 'revoked_at', 'revocation_reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function device(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RegisteredDevice::class, 'device_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
