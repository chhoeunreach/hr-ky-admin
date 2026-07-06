<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'embedding',
        'embedding_dimension',
        'model_version',
        'quality_score',
        'is_active',
        'enrolled_by_device_id',
        'enrolled_at',
    ];

    protected $casts = [
        'embedding' => 'encrypted:array',
        'embedding_dimension' => 'integer',
        'quality_score' => 'float',
        'is_active' => 'boolean',
        'enrolled_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function enrolledByDevice(): BelongsTo
    {
        return $this->belongsTo(KioskDevice::class, 'enrolled_by_device_id');
    }
}
