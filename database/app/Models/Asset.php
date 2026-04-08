<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'name',
        'type_id',
        'image',
        'asset_code',
        'asset_serial_no',
        'is_working',
        'purchased_date',
        'warranty_available',
        'warranty_end_date',
        'is_available',
        'assigned_to',
        'assigned_date',
        'note',
        'created_by',
        'updated_by'
    ];

    const IS_WORKING = ['yes','no','maintenance'];

    const BOOLEAN_DATA = [
        0 => 'no',
        1 => 'yes'
    ];

    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/asset/';

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

        if (Auth::check() && Auth::user()->id != 1 && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('assignedTo', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

            });
        }
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class,'type_id','id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class,'assigned_to','id')->withDefault();
    }

}
