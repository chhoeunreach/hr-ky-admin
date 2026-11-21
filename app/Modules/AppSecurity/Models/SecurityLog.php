<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    protected $table = 'app_security_logs';

    protected $fillable = [
        'event_type', 'severity', 'user_id', 'device_id', 'request_uuid',
        'ip_address', 'details', 'occurred_at',
    ];

    protected $casts = [
        'details' => 'array',
        'occurred_at' => 'datetime',
    ];
}
