<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Tada extends Model
{
    use HasFactory;

    const RECORDS_PER_PAGE = 20;

    const STATUS = ['pending', 'accepted', 'rejected'];

    protected $table = 'tadas';

    protected $fillable = [
        'title',
        'description',
        'total_expense',
        'status',
        'is_active',
        'is_settled',
        'remark',
        'employee_id',
        'verified_by',
        'created_by',
        'updated_by'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id;
            $model->updated_by = Auth::user()->id;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id;
        });

        static::deleting(function ($tadaDetail) {
            $tadaDetail->attachments()->delete();
        });
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');

    }


    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TadaAttachment::class, 'tada_id', 'id');
    }

}
