<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationInterviewMessage extends Model
{
    use HasFactory;

    protected $fillable = ['interview_id', 'role', 'message'];

    public function interview()
    {
        return $this->belongsTo(EvaluationInterview::class, 'interview_id');
    }
}
