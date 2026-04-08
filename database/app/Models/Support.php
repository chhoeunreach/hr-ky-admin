<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Support extends Model
{
    use HasFactory;

    protected $table = 'supports';

    protected $fillable = [
        'title',
        'description',
        'is_seen',
        'status',
        'department_id',
        'created_by',
        'updated_by'
    ];

    const RECORDS_PER_PAGE = 20;

    const STATUS = ['pending','in_progress','solved'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id;
//            $model->updated_by = Auth::user()->id;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id;
        });

        if (Auth::check() && Auth::user()->id != 1 && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('createdBy', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

            });
        }

    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }

    public function departmentQuery(): BelongsTo
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }


}
