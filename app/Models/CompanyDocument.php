<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanyDocument extends Model
{
    use HasFactory;

    const UPLOAD_PATH = 'uploads/company-documents/';

    const CATEGORY_MASTER = 'master';
    const CATEGORY_TAX = 'tax';
    const CATEGORY_BUSINESS_LICENSE = 'business_license';
    const CATEGORY_NSSF = 'nssf';
    const CATEGORY_CONTRACT = 'contract';
    const CATEGORY_UTILITY = 'utility';

    const SUB_TYPE_NATIONAL_ID = 'national_id';
    const SUB_TYPE_PASSPORT = 'passport';
    const SUB_TYPE_FAMILY_BOOK = 'family_book';
    const SUB_TYPE_RESIDENCE_BOOK = 'residence_book';
    const SUB_TYPE_OTHER_MASTER = 'other_master';

    const SUB_TYPE_RENT = 'rent';
    const SUB_TYPE_ELECTRICITY = 'electricity';
    const SUB_TYPE_WATER = 'water';
    const SUB_TYPE_INTERNET = 'internet';
    const SUB_TYPE_WASTE = 'waste';

    const STATUS_VALID = 'valid';
    const STATUS_EXPIRING_SOON = 'expiring_soon';
    const STATUS_EXPIRED = 'expired';
    const STATUS_ARCHIVED = 'archived';

    protected $table = 'company_documents';

    protected $fillable = [
        'category_code',
        'doc_sub_type',
        'branch_id',
        'company_id',
        'doc_number',
        'title',
        'holder_name',
        'issuing_authority',
        'issue_date',
        'expiry_date',
        'alert_before_days',
        'cost_amount',
        'currency',
        'receipt_number',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'confidentiality_level',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'alert_before_days' => 'integer',
        'cost_amount' => 'decimal:2',
        'file_size' => 'integer',
    ];

    protected $appends = [
        'calculated_status',
        'days_remaining',
        'file_url',
        'is_pdf',
        'is_image',
        'doc_sub_type_label',
    ];

    public function getDocSubTypeLabelAttribute(): ?string
    {
        if (!$this->doc_sub_type) return null;
        return match ($this->doc_sub_type) {
            self::SUB_TYPE_NATIONAL_ID => __('index.doc_type_national_id'),
            self::SUB_TYPE_PASSPORT => __('index.doc_type_passport'),
            self::SUB_TYPE_FAMILY_BOOK => __('index.doc_type_family_book'),
            self::SUB_TYPE_RESIDENCE_BOOK => __('index.doc_type_residence_book'),
            self::SUB_TYPE_RENT => __('index.doc_type_rent'),
            self::SUB_TYPE_ELECTRICITY => __('index.doc_type_electricity'),
            self::SUB_TYPE_WATER => __('index.doc_type_water'),
            self::SUB_TYPE_INTERNET => __('index.doc_type_internet'),
            self::SUB_TYPE_WASTE => __('index.doc_type_waste'),
            default => __('index.doc_type_other_master'),
        };
    }

    public function category()
    {
        return $this->belongsTo(CompanyDocumentCategory::class, 'category_code', 'code');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Compute dynamic real-time status based on expiry_date
     */
    public function getCalculatedStatusAttribute(): string
    {
        if ($this->status === self::STATUS_ARCHIVED) {
            return self::STATUS_ARCHIVED;
        }

        if (!$this->expiry_date) {
            return self::STATUS_VALID;
        }

        $today = Carbon::today();
        $expiry = Carbon::parse($this->expiry_date)->startOfDay();

        if ($expiry->isPast()) {
            return self::STATUS_EXPIRED;
        }

        $alertDays = $this->alert_before_days ?: 30;
        $threshold = $today->copy()->addDays($alertDays);

        if ($expiry->lessThanOrEqualTo($threshold)) {
            return self::STATUS_EXPIRING_SOON;
        }

        return self::STATUS_VALID;
    }

    /**
     * Number of days until expiry (positive = remaining, negative = overdue)
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        $today = Carbon::today();
        $expiry = Carbon::parse($this->expiry_date)->startOfDay();

        return (int) $today->diffInDays($expiry, false);
    }

    public function getFileUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '';
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return asset($this->file_path);
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_type ?? '') === 'pdf'
            || str_ends_with(strtolower($this->file_path ?? ''), '.pdf');
    }

    public function getIsImageAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_path ?? '', PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
    }

    public function getFormattedCostAttribute(): ?string
    {
        if (is_null($this->cost_amount)) {
            return null;
        }

        if ($this->currency === 'KHR') {
            return number_format($this->cost_amount, 0) . ' ៛';
        }

        return '$' . number_format($this->cost_amount, 2);
    }
}
