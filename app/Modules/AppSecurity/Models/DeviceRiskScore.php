<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceRiskScore extends Model
{
    protected $table = 'app_device_risk_scores';

    protected $fillable = ['device_id', 'risk_score', 'risk_level', 'policy_version', 'reasons', 'calculated_at'];

    protected $casts = [
        'risk_score' => 'integer',
        'reasons' => 'array',
        'calculated_at' => 'datetime',
    ];
}
