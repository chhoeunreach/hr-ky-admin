<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeInterview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'interview_date',
        'interview_stage',
        'interviewer_id',
        'interviewer_name',
        'interviewer_position',
        'recruitment_source',
        'result',
        'score',
        'comments',
        'final_approved_by',
        'created_by',
    ];

    protected $casts = ['interview_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
