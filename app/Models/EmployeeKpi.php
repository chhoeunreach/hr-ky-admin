<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeKpi extends Model
{
    use HasFactory;

    protected $table = 'employee_kpis';

    protected $fillable = [
        'employee_id',
        'review_period_id',
        'name',
        'description',
        'target_value',
        'actual_value',
        'unit',
        'weight',
        'achievement_percentage',
        'score',
        'manager_comment',
        'created_by',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
