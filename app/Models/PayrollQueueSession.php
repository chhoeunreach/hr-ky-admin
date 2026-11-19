<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollQueueSession extends Model
{
    use HasFactory;

    protected $table = 'payroll_queue_sessions';

    protected $fillable = [
        'session_code',
        'company_id',
        'branch_id',
        'month',
        'title',
        'qr_token',
        'status',
        'max_active_serving',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'max_active_serving' => 'integer',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(PayrollQueueTicket::class, 'session_id')->orderBy('id', 'asc');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function waitingTickets()
    {
        return $this->tickets()->where('status', 'waiting')->orderBy('joined_at', 'asc')->orderBy('id', 'asc');
    }

    public function servingTickets()
    {
        return $this->tickets()->whereIn('status', ['calling', 'serving'])->orderBy('counter_number', 'asc');
    }

    public function paidTickets()
    {
        return $this->tickets()->where('status', 'paid')->orderBy('paid_at', 'desc')->orderBy('id', 'desc');
    }
}
