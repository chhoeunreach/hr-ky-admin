<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeePayslip extends Model
{
    use HasFactory;

    protected $table = 'employee_payslips';

    const UPLOAD_PATH = 'uploads/payslip/';

    public $timestamps = true;

    protected $fillable = [

        'employee_id',
        'paid_on',
        'status',
        'remark',
        'salary_group_id',
        'salary_cycle',
        'salary_from',
        'salary_to',
        'gross_salary',
        'tds',
        'advance_salary',
        'tada',
        'net_salary',
        'total_days',
        'present_days',
        'absent_days',
        'leave_days',
        'created_by',
        'updated_by',
        'payment_method_id',
        'include_tada',
        'include_advance_salary',
        'attendance',
        'absent_paid',
        'approved_paid_leaves',
        'absent_deduction',
        'holidays',
        'weekends',
        'paid_leave',
        'unpaid_leave',
        'overtime',
        'undertime',
        'created_at',
        'updated_at',
        'is_bs_enabled',
        'ssf_deduction',
        'ssf_contribution',
        'bonus'

    ];

    public function payslipDetail():HasMany
    {
        return $this->hasMany(EmployeePayslipDetail::class, 'employee_payslip_id', 'id');
    }

    public function salaryGroup()
    {
        return $this->belongsTo(SalaryGroup::class,'salary_group_id','id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');
    }
}
