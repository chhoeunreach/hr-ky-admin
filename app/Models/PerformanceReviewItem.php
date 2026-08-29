<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReviewItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_review_id',
        'category',
        'criteria',
        'description',
        'max_score',
        'score',
        'weight',
        'comment',
        'sort_order',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'performance_review_id', 'id');
    }
}
