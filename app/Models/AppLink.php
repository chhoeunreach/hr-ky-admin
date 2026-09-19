<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AppLink extends Model
{
    use HasFactory;

    protected $table = 'app_links';

    public const UPLOAD_PATH = 'uploads/links/';

    public const LINK_TYPES = [
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'telegram' => 'Telegram',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'website' => 'Website',
        'other' => 'Other',
    ];

    const RECORDS_PER_PAGE = 20;

    protected $fillable = [
        'name',
        'link_type',
        'url',
        'image',
        'description',
        'order',
        'status',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
            if (empty($model->company_id) && Auth::check()) {
                $model->company_id = Auth::user()->company_id ?? null;
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(public_path(self::UPLOAD_PATH . $this->image))) {
            return asset(self::UPLOAD_PATH . $this->image);
        }
        return null;
    }
}
