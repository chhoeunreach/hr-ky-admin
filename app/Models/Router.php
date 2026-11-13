<?php

namespace App\Models;

use App\Helpers\AppHelper;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Router extends Model
{
    use HasFactory;

    protected $table = 'routers';

    protected $fillable = [
        'router_ssid',
        'company_id',
        'branch_id',
        'is_active'
    ];

    const RECORDS_PER_PAGE = 20;

    public static function boot()
    {
        parent::boot();

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                if (AppHelper::canAccessAllBranches()) {
                    return;
                }
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->whereIn('id', AppHelper::accessibleBranchIds());
                });
            });
        }
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id','id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }
}
