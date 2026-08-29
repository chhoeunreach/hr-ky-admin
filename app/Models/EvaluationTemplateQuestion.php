<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationTemplateQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'section',
        'question_kh',
        'question_en',
        'question_type',
        'weight',
        'max_score',
        'reason',
        'sort_order',
        'is_active',
    ];

    public function template()
    {
        return $this->belongsTo(EvaluationTemplate::class, 'template_id');
    }
}
