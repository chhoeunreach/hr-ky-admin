<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SellOutReportPhoto extends Model
{
    use HasFactory;

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

    public function getPhotoUrlAttribute(?string $value): string
    {
        if ($value) {
            return $value;
        }

        return Storage::disk('public')->url($this->photo_path);
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
