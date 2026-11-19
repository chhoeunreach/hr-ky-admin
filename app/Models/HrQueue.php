<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HrQueue extends Model
{
    use HasFactory;

    protected $table = 'hr_queues';

    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
        'date',
        'ticket_number',
        'counter_number',
        'status',
        'called_at',
        'served_at',
        'completed_at',
        'qr_payload',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'counter_number' => 'integer',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function disbursement(): HasOne
    {
        return $this->hasOne(PayrollDisbursement::class, 'queue_ticket_id');
    }
}
