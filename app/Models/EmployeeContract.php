<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'contract_no',
        'contract_date',
        'shop_name',
        'shop_address',
        'shop_representative',
        'birth_address',
        'guardian_phone',
        'job_title',
        'main_responsibilities',
        'additional_responsibilities',
        'asset_responsibilities',
        'probation_salary',
        'extra_salary',
        'monthly_salary',
        'salary_currency',
        'probation_period_text',
        'main_contract_period',
        'contract_start_date',
        'contract_end_date',
        'payment_date_text',
        'benefits',
        'working_time',
        'working_days',
        'holiday_text',
        'discipline_rules',
        'confidentiality',
        'termination_terms',
        'general_duties',
        'party_a_signature_name',
        'party_b_signature_name',
        'party_a_signed_date',
        'party_b_signed_date',
        'attachments',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'party_a_signed_date' => 'date',
        'party_b_signed_date' => 'date',
        'attachments' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(EmployeeContractHistory::class, 'employee_contract_id', 'id');
    }
}
