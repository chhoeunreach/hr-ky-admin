<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationInterview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'department_id',
        'position_id',
        'branch_id',
        'evaluator_id',
        'evaluation_period',
        'status',
        'ai_summary',
    ];

    protected $casts = ['ai_summary' => 'array'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function messages()
    {
        return $this->hasMany(EvaluationInterviewMessage::class, 'interview_id');
    }

    public function jobDescription()
    {
        return $this->hasOne(EvaluationJobDescription::class, 'interview_id');
    }
}
