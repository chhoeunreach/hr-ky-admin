<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Trainer extends Model
{
    use HasFactory;
    protected $table = 'trainers';
    protected $fillable = [
        'trainer_type', 'branch_id', 'department_id', 'employee_id', 'name', 'contact_number', 'email', 'expertise', 'address', 'created_by', 'updated_by','status'
    ];


    const RECORDS_PER_PAGE = 20;

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('trainer', function (Builder $builder) {
            $builder->when(Auth::check() && Auth::user()->id != 1, function ($query) {
                $userBranchId = Auth::user()->branch_id ?? null;
                $query->where(function ($subquery) use ($userBranchId) {
                    $subquery->where('trainer_type', 'internal')
                        ->where(function ($branchQuery) use ($userBranchId) {
                            $branchQuery->where('branch_id', $userBranchId)
                                ->orWhereNull('branch_id');
                        })
                        ->orWhere(function ($externalQuery) use ($userBranchId) {
                            $externalQuery->where('trainer_type', 'external')
                                ->whereHas('createdBy', function ($createdByQuery) use ($userBranchId) {
                                    $createdByQuery->where('branch_id', $userBranchId);
                                });
                        });
                });
            });
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class,'branch_id','id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
