<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KioskDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'token_prefix',
        'token_hash',
        'admin_pin_hash',
        'is_active',
        'last_seen_at',
        'provisioned_at',
        'expires_at',
    ];

    protected $hidden = [
        'token_hash',
        'admin_pin_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'provisioned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function faceProfiles(): HasMany
    {
        return $this->hasMany(FaceProfile::class, 'enrolled_by_device_id');
    }

    public function attendanceEvents(): HasMany
    {
        return $this->hasMany(KioskAttendanceEvent::class);
    }
}
