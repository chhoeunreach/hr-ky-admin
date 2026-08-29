<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDisciplinaryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'incident_date',
        'record_type',
        'severity',
        'title',
        'description',
        'action_taken',
        'warning_level',
        'issued_by',
        'approved_by',
        'status',
        'employee_acknowledged_at',
        'attachment',
    ];

    protected $casts = ['incident_date' => 'date', 'employee_acknowledged_at' => 'datetime'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
