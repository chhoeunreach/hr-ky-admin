<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTrainingHistory extends Model
{
    use HasFactory;

    protected $table = 'employee_training_history';

    protected $fillable = [
        'employee_id',
        'training_date',
        'training_title',
        'training_type',
        'trainer_name',
        'trainer_employee_id',
        'provider',
        'objective',
        'result',
        'score',
        'certificate',
        'note',
        'created_by',
    ];

    protected $casts = ['training_date' => 'date'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
