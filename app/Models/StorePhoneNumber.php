<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePhoneNumber extends Model
{
    use HasFactory;

    protected $table = 'store_phone_numbers';

    protected $fillable = [
        'phone_number',
        'operator',
        'sim_slot',
        'purpose',
        'plan_type',
        'serial_number',
        'asset_id',
        'branch_id',
        'assigned_to',
        'assigned_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    public const OPERATORS = [
        'smart' => 'Smart',
        'cellcard' => 'Cellcard',
        'metfone' => 'Metfone',
        'seatel' => 'YES / Seatel',
        'other' => 'Other',
    ];

    public const SIM_SLOTS = [
        'sim_1' => 'SIM 1',
        'sim_2' => 'SIM 2',
        'esim' => 'eSIM',
        'standalone' => 'Standalone SIM',
    ];

    public const PURPOSES = [
        'sale' => 'Sales & Orders',
        'cashier' => 'Cashier & Payments',
        'delivery' => 'Delivery & Dispatch',
        'hotline' => 'Branch Hotline',
        'customer_service' => 'Customer Service',
        'manager' => 'Managerial',
        'technical' => 'Technical / Support',
        'general' => 'General Store Line',
        'other' => 'Other',
    ];

    public const PLAN_TYPES = [
        'prepaid' => 'Prepaid',
        'postpaid' => 'Postpaid',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'reserved' => 'Reserved',
        'lost' => 'Lost / Blocked',
    ];

    public static function getTranslatedOperators(): array
    {
        return [
            'smart' => 'Smart',
            'cellcard' => 'Cellcard',
            'metfone' => 'Metfone',
            'seatel' => 'YES / Seatel',
            'other' => __('index.operator_other'),
        ];
    }

    public static function getTranslatedSimSlots(): array
    {
        return [
            'sim_1' => __('index.slot_sim_1'),
            'sim_2' => __('index.slot_sim_2'),
            'esim' => __('index.slot_esim'),
            'standalone' => __('index.slot_standalone'),
        ];
    }

    public static function getTranslatedPurposes(): array
    {
        return [
            'sale' => __('index.purpose_sale'),
            'cashier' => __('index.purpose_cashier'),
            'delivery' => __('index.purpose_delivery'),
            'hotline' => __('index.purpose_hotline'),
            'customer_service' => __('index.purpose_customer_service'),
            'manager' => __('index.purpose_manager'),
            'technical' => __('index.purpose_technical'),
            'general' => __('index.purpose_general'),
            'other' => __('index.purpose_other'),
        ];
    }

    public static function getTranslatedPlanTypes(): array
    {
        return [
            'prepaid' => __('index.plan_prepaid'),
            'postpaid' => __('index.plan_postpaid'),
        ];
    }

    public static function getTranslatedStatuses(): array
    {
        return [
            'active' => __('index.active'),
            'inactive' => __('index.inactive'),
            'reserved' => __('index.reserved'),
            'lost' => __('index.lost_damaged'),
        ];
    }

    public function getOperatorLabelAttribute(): string
    {
        $items = self::getTranslatedOperators();
        return $items[$this->operator] ?? ucfirst($this->operator ?? '');
    }

    public function getSimSlotLabelAttribute(): string
    {
        $items = self::getTranslatedSimSlots();
        return $items[$this->sim_slot] ?? strtoupper($this->sim_slot ?? '');
    }

    public function getPurposeLabelAttribute(): string
    {
        if (!$this->purpose) {
            return '';
        }
        $items = self::getTranslatedPurposes();
        return $items[$this->purpose] ?? ucfirst($this->purpose);
    }

    public function getPlanTypeLabelAttribute(): string
    {
        $items = self::getTranslatedPlanTypes();
        return $items[$this->plan_type] ?? ucfirst($this->plan_type ?? '');
    }

    public function getStatusLabelAttribute(): string
    {
        $items = self::getTranslatedStatuses();
        return $items[$this->status] ?? ucfirst($this->status ?? '');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('employee', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('asset', fn ($aq) => $aq->where('name', 'like', "%{$search}%")->orWhere('asset_code', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['operator'])) {
            $query->where('operator', $filters['operator']);
        }

        if (!empty($filters['purpose'])) {
            $query->where('purpose', $filters['purpose']);
        }

        if (!empty($filters['sim_slot'])) {
            $query->where('sim_slot', $filters['sim_slot']);
        }

        if (isset($filters['is_assigned']) && $filters['is_assigned'] !== '') {
            if ($filters['is_assigned'] == '1') {
                $query->whereNotNull('assigned_to');
            } elseif ($filters['is_assigned'] == '0') {
                $query->whereNull('assigned_to');
            }
        }

        if (isset($filters['is_standalone']) && $filters['is_standalone'] !== '') {
            if ($filters['is_standalone'] == '1') {
                $query->whereNull('asset_id');
            } elseif ($filters['is_standalone'] == '0') {
                $query->whereNotNull('asset_id');
            }
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['asset_id'])) {
            $query->where('asset_id', $filters['asset_id']);
        }

        return $query;
    }
}
