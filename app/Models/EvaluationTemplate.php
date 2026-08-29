<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_description_id',
        'employee_id',
        'position_id',
        'department_id',
        'branch_id',
        'title',
        'version',
        'evaluation_type',
        'status',
        'created_by',
    ];

    public function questions()
    {
        return $this->hasMany(EvaluationTemplateQuestion::class, 'template_id')->orderBy('sort_order');
    }

    public function jobDescription()
    {
        return $this->belongsTo(EvaluationJobDescription::class, 'job_description_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
