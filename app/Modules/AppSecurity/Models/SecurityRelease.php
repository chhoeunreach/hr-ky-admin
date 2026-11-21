<?php

namespace App\Modules\AppSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityRelease extends Model
{
    protected $table = 'app_security_releases';

    protected $fillable = [
        'uuid', 'package_name', 'version_name', 'version_code', 'signing_sha256',
        'apk_sha256', 'apk_size', 'download_path', 'release_notes',
        'minimum_supported_version', 'minimum_android_sdk', 'target_android_sdk',
        'release_status', 'is_mandatory', 'released_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'minimum_supported_version' => 'integer',
        'minimum_android_sdk' => 'integer',
        'target_android_sdk' => 'integer',
        'apk_size' => 'integer',
        'is_mandatory' => 'boolean',
        'released_at' => 'datetime',
    ];
}
