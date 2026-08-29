<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryHistory extends Model
{
    use HasFactory;

    protected $table = 'employee_salary_history';

    protected $fillable = [
        'employee_id',
        'effective_date',
        'old_base_salary',
        'increase_amount',
        'increase_percentage',
        'new_base_salary',
        'allowance_before',
        'allowance_after',
        'reason',
        'requested_by',
        'reviewed_by',
        'approved_by',
        'approval_status',
        'note',
    ];

    protected $casts = ['effective_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
