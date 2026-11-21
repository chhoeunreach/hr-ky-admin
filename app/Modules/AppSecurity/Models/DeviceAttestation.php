<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAttestation extends Model
{
    protected $table = 'app_device_attestations';

    protected $fillable = [
        'device_id', 'challenge_id', 'evidence_encrypted', 'verification_status',
        'security_level', 'verification_summary', 'verified_at', 'evidence_expires_at',
    ];

    protected $casts = [
        'verification_summary' => 'array',
        'verified_at' => 'datetime',
        'evidence_expires_at' => 'datetime',
    ];
}
