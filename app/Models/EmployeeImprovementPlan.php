<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeImprovementPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'performance_review_id',
        'reason',
        'start_date',
        'end_date',
        'expectations',
        'support_required',
        'progress_notes',
        'status',
        'created_by',
        'approved_by',
        'completed_at',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'completed_at' => 'datetime'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
