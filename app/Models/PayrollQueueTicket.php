<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollQueueTicket extends Model
{
    use HasFactory;

    protected $table = 'payroll_queue_tickets';

    protected $fillable = [
        'session_id',
        'employee_id',
        'payslip_id',
        'ticket_number',
        'counter_number',
        'status',
        'joined_at',
        'called_at',
        'served_at',
        'paid_at',
        'payment_method_id',
        'net_amount_paid',
        'disbursed_by',
        'remarks',
    ];

    protected $casts = [
        'counter_number' => 'integer',
        'net_amount_paid' => 'decimal:2',
        'joined_at' => 'datetime',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PayrollQueueSession::class, 'session_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(EmployeePayslip::class, 'payslip_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
