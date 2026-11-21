<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RegisteredDevice extends Model
{
    protected $table = 'app_registered_devices';

    protected $fillable = [
        'uuid', 'user_id', 'user_type', 'installation_uuid', 'public_key',
        'public_key_fingerprint', 'package_name', 'signing_sha256', 'version_name',
        'version_code', 'android_sdk', 'android_version', 'device_model',
        'attestation_level', 'device_status', 'pre_block_status', 'risk_score', 'approved_by',
        'approved_at', 'last_seen_at', 'registered_at', 'blocked_at', 'revoked_at',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'android_sdk' => 'integer',
        'risk_score' => 'integer',
        'approved_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'registered_at' => 'datetime',
        'blocked_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function attestations()
    {
        return $this->hasMany(DeviceAttestation::class, 'device_id');
    }

    public function latestRiskScore(): HasOne
    {
        return $this->hasOne(DeviceRiskScore::class, 'device_id')->latestOfMany('calculated_at');
    }

    public function riskScores()
    {
        return $this->hasMany(DeviceRiskScore::class, 'device_id');
    }
}
