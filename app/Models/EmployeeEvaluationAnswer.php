<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEvaluationAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'question_id',
        'section',
        'question_kh',
        'question_en',
        'weight',
        'max_score',
        'score',
        'is_na',
        'comment',
        'weighted_score',
        'sort_order',
    ];

    protected $casts = ['is_na' => 'boolean'];

    public function evaluation()
    {
        return $this->belongsTo(EmployeeEvaluation::class, 'evaluation_id');
    }
}
