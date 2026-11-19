<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDisbursement extends Model
{
    use HasFactory;

    protected $table = 'payroll_disbursements';

    protected $fillable = [
        'user_id',
        'payslip_id',
        'queue_ticket_id',
        'month',
        'disbursed_at',
        'disbursed_by',
        'basic_salary',
        'leave_penalty',
        'late_penalty',
        'loan_deduction',
        'installment_deduction',
        'total_deductions',
        'sales_commission',
        'iron_commission',
        'material_commission',
        'attendance_bonus',
        'punctuality_bonus',
        'total_bonuses',
        'net_received_salary',
        'payment_method',
        'transaction_ref',
        'status',
        'notes',
    ];

    protected $casts = [
        'disbursed_at' => 'datetime',
        'basic_salary' => 'decimal:2',
        'leave_penalty' => 'decimal:2',
        'late_penalty' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'installment_deduction' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'sales_commission' => 'decimal:2',
        'iron_commission' => 'decimal:2',
        'material_commission' => 'decimal:2',
        'attendance_bonus' => 'decimal:2',
        'punctuality_bonus' => 'decimal:2',
        'total_bonuses' => 'decimal:2',
        'net_received_salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function queueTicket(): BelongsTo
    {
        return $this->belongsTo(HrQueue::class, 'queue_ticket_id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(EmployeePayslip::class, 'payslip_id');
    }

    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
