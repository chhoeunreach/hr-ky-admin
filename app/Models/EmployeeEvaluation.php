<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'job_description_id',
        'employee_id',
        'evaluator_id',
        'evaluation_period',
        'evaluation_type',
        'status',
        'total_score',
        'result_label',
        'ai_summary',
        'evaluator_comment',
        'strengths',
        'areas_for_improvement',
        'next_review_goals',
        'support_needed',
        'final_decision',
        'decision_reason',
        'completed_at',
    ];

    protected $casts = ['ai_summary' => 'array', 'completed_at' => 'datetime'];

    public function answers()
    {
        return $this->hasMany(EmployeeEvaluationAnswer::class, 'evaluation_id')->orderBy('sort_order');
    }

    public function template()
    {
        return $this->belongsTo(EvaluationTemplate::class, 'template_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
