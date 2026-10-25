<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DCardEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'name_khmer',
        'name_english',
        'position_khmer',
        'position_english',
        'department',
        'branch',
        'joining_date',
        'emergency_contact',
        'blood_type',
        'khqr_account_id',
        'profile_photo_url',
        'phone',
        'email',
    ];

    protected $casts = [
        'joining_date' => 'date:Y-m-d',
    ];

    public function getProfilePhotoUrlAttribute(?string $value): ?string
    {
        $path = $this->normalizePhotoPath($value);

        if ($path && preg_match('#^/(?:storage/d-card-employees/photos/|uploads/user/avatar/|assets/images/)#', $path)) {
            return request()->root() . $path;
        }

        return $path;
    }

    public function setProfilePhotoUrlAttribute(?string $value): void
    {
        $this->attributes['profile_photo_url'] = $this->normalizePhotoPath($value);
    }

    private function normalizePhotoPath(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return $value;
        }

        $value = trim($value);
        $path = parse_url($value, PHP_URL_PATH);

        // These photos belong to this application, even when an old host was saved.
        if (is_string($path) && preg_match('#^/?(?:storage/d-card-employees/photos/|uploads/user/avatar/|assets/images/)#', $path)) {
            return '/' . ltrim($path, '/');
        }

        return $value;
    }
}
