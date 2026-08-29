<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'review_type',
        'period_start',
        'period_end',
        'review_date',
        'evaluator_id',
        'department_head_id',
        'hr_approver_id',
        'total_score',
        'grade',
        'status',
        'strengths',
        'areas_for_improvement',
        'manager_comment',
        'employee_comment',
        'final_recommendation',
        'next_review_date',
        'employee_acknowledged_at',
        'approved_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'review_date' => 'date',
        'next_review_date' => 'date',
        'employee_acknowledged_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(PerformanceReviewItem::class)->orderBy('sort_order');
    }
}
