<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellOutReportPhoto extends Model
{
    use HasFactory;

    const UPLOAD_PATH = 'uploads/sell-out-reports/';

    protected $fillable = [
        'sell_out_report_id',
        'sell_out_report_line_id',
        'photo_path',
        'photo_url',
        'original_name',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): string
    {
        return asset(self::UPLOAD_PATH . $this->photo_path);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(SellOutReport::class, 'sell_out_report_id');
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(SellOutReportLine::class, 'sell_out_report_line_id');
    }
}
