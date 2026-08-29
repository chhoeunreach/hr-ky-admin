<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmploymentHistory extends Model
{
    use HasFactory;

    protected $table = 'employee_employment_history';

    protected $fillable = [
        'employee_id',
        'effective_date',
        'old_position_id',
        'new_position_id',
        'old_department_id',
        'new_department_id',
        'old_branch_id',
        'new_branch_id',
        'old_manager_id',
        'new_manager_id',
        'change_type',
        'reason',
        'requested_by',
        'approved_by',
        'note',
    ];

    protected $casts = ['effective_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
