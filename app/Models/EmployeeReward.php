<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reward_date',
        'reward_type',
        'title',
        'description',
        'reward_amount',
        'approved_by',
        'created_by',
    ];

    protected $casts = ['reward_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
