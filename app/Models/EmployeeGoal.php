<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'performance_review_id',
        'title',
        'description',
        'target',
        'start_date',
        'due_date',
        'progress',
        'status',
        'assigned_by',
        'employee_comment',
        'manager_comment',
        'completed_at',
    ];

    protected $casts = ['start_date' => 'date', 'due_date' => 'date', 'completed_at' => 'datetime'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
