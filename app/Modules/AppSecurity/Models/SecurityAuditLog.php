<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAuditLog extends Model
{
    protected $table = 'app_security_audit_logs';

    protected $fillable = [
        'admin_id', 'actor_type', 'actor_id', 'action', 'target_type', 'target_id', 'previous_state',
        'new_state', 'reason', 'ip_address', 'user_agent', 'occurred_at',
    ];

    protected $casts = [
        'previous_state' => 'array',
        'new_state' => 'array',
        'occurred_at' => 'datetime',
    ];
}
