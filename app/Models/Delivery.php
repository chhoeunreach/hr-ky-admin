<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const TYPE_CUSTOMER = 'customer';
    const TYPE_BRANCH = 'branch';

    protected $table = 'deliveries';

    protected $fillable = [
        'user_id',
        'branch_id',
        'destination_branch_id',
        'delivery_type',
        'tracking_token',
        'tracking_code',
        'invoice_number',
        'customer_name',
        'customer_phone',
        'destination_address',
        'destination_latitude',
        'destination_longitude',
        'origin_address',
        'origin_latitude',
        'origin_longitude',
        'current_latitude',
        'current_longitude',
        'heading',
        'speed',
        'last_ping_at',
        'status',
        'started_at',
        'completed_at',
        'proof_photo_path',
        'driver_selfie',
        'completion_selfie',
        'product_photos',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'destination_branch_id' => 'integer',
        'destination_latitude' => 'float',
        'destination_longitude' => 'float',
        'origin_latitude' => 'float',
        'origin_longitude' => 'float',
        'current_latitude' => 'float',
        'current_longitude' => 'float',
        'heading' => 'float',
        'speed' => 'float',
        'product_photos' => 'array',
        'last_ping_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'tracking_url',
        'is_active',
        'status_label',
        'formatted_duration',
        'proof_photo_url',
        'driver_selfie_url',
        'completion_selfie_url',
        'product_photo_urls',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function destinationBranch()
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function getTrackingUrlAttribute(): string
    {
        return url('/track/live/' . $this->tracking_token);
    }

    public function getProofPhotoUrlAttribute(): ?string
    {
        if (!$this->proof_photo_path) return null;
        if (str_starts_with($this->proof_photo_path, 'http')) return $this->proof_photo_path;
        return asset($this->proof_photo_path);
    }

    public function getDriverSelfieUrlAttribute(): ?string
    {
        if (!$this->driver_selfie) return null;
        if (str_starts_with($this->driver_selfie, 'http')) return $this->driver_selfie;
        return asset($this->driver_selfie);
    }

    public function getCompletionSelfieUrlAttribute(): ?string
    {
        if (!$this->completion_selfie) return null;
        if (str_starts_with($this->completion_selfie, 'http')) return $this->completion_selfie;
        return asset($this->completion_selfie);
    }

    public function getProductPhotoUrlsAttribute(): array
    {
        if (!$this->product_photos || !is_array($this->product_photos)) return [];
        return array_values(array_map(function ($path) {
            if (str_starts_with($path, 'http')) return $path;
            return asset($path);
        }, $this->product_photos));
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_TRANSIT, self::STATUS_ARRIVED]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'រង់ចាំ (Pending)',
            self::STATUS_IN_TRANSIT => 'កំពុងដឹកជញ្ជូន (In Transit)',
            self::STATUS_ARRIVED => 'បានមកដល់ (Arrived)',
            self::STATUS_DELIVERED => 'បានប្រគល់ជោគជ័យ (Delivered)',
            self::STATUS_CANCELLED => 'បានបោះបង់ (Cancelled)',
            default => $this->status,
        };
    }

    public function getFormattedDurationAttribute(): string
    {
        $start = $this->started_at ?? $this->created_at;
        if (!$start) return '0 នាទី';
        $end = $this->completed_at ?? Carbon::now();
        $diff = $start->diff($end);
        if ($diff->h > 0) {
            return "{$diff->h} ម៉ោង {$diff->i} នាទី";
        }
        return "{$diff->i} នាទី";
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_TRANSIT, self::STATUS_ARRIVED]);
    }
}
