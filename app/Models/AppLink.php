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

    public const ACCOUNT_TYPES = [
        'telegram' => ['personal' => 'Personal', 'group' => 'Group', 'channel' => 'Channel'],
        'facebook' => ['person' => 'Person', 'page' => 'Page', 'group' => 'Group'],
    ];

    protected $hidden = ['username', 'phone_number', 'email', 'password'];

    protected $fillable = [
        'name',
        'link_type',
        'account_type',
        'username',
        'phone_number',
        'email',
        'password',
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
        'password' => 'encrypted',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $actor = Auth::guard('admin')->user() ?: Auth::user();
            $model->created_by_name = $actor?->name;
            $model->created_by = Auth::user()->id ?? null;
            if (empty($model->company_id) && Auth::check()) {
                $model->company_id = Auth::user()->company_id ?? null;
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withTrashed();
    }

    public function managers()
    {
        return $this->belongsToMany(User::class, 'app_link_managers')->withTimestamps();
    }

    public static function managerOptions(?self $link = null)
    {
        return User::where(fn ($query) => $query->where('is_active', 1)
            ->orWhereIn('id', $link?->managers->modelKeys() ?? []))
            ->orderBy('name')->get(['id', 'name', 'english_name', 'employee_code']);
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
