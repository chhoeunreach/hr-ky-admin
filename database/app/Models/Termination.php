<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Termination extends Model
{
    use HasFactory;
    protected $table = 'terminations';

    protected $fillable = [
        'employee_id','termination_type_id','notice_date','termination_date','reason','status','admin_remark','created_by','updated_by','document'
    ];

    const RECORDS_PER_PAGE = 20;
    const UPLOAD_PATH = 'uploads/termination/';

    public static function boot()
    {
        parent::boot();

        if (Auth::check() && Auth::user()->id != 1 && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('employee', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

            });
        }
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
    public function terminationType(): BelongsTo
    {
        return $this->belongsTo(TerminationType::class, 'termination_type_id', 'id');
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
