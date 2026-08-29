<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationJobDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_id',
        'employee_id',
        'position_id',
        'department_id',
        'branch_id',
        'job_title',
        'main_purpose',
        'responsibilities',
        'daily_tasks',
        'weekly_tasks',
        'monthly_tasks',
        'kpis',
        'required_skills',
        'required_knowledge',
        'tools',
        'common_problems',
        'customer_responsibilities',
        'reporting_responsibilities',
        'leadership_responsibilities',
        'special_responsibilities',
        'version',
        'status',
        'document_number',
        'ai_generated',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'responsibilities' => 'array',
        'daily_tasks' => 'array',
        'weekly_tasks' => 'array',
        'monthly_tasks' => 'array',
        'kpis' => 'array',
        'required_skills' => 'array',
        'required_knowledge' => 'array',
        'tools' => 'array',
        'common_problems' => 'array',
        'customer_responsibilities' => 'array',
        'reporting_responsibilities' => 'array',
        'leadership_responsibilities' => 'array',
        'special_responsibilities' => 'array',
        'ai_generated' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function interview()
    {
        return $this->belongsTo(EvaluationInterview::class, 'interview_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
