<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function branch()
    {
        return $this->belongsTo(Branch::class,'branch_id','id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }
}
