<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'national_id',
        'nationality',
        'education_level',
        'telegram',
        'current_address',
        'permanent_address',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'employment_status',
        'last_working_date',
        'employment_end_reason',
        'probation_period',
        'probation_end_date',
        'contract_start_date',
        'contract_end_date',
        'weekly_day_off',
        'starting_salary',
        'current_base_salary',
        'allowances',
        'commission',
        'attendance_bonus',
        'punctuality_bonus',
        'overtime',
        'other_benefits',
        'payment_method',
        'salary_payment_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'probation_end_date' => 'date',
        'last_working_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
