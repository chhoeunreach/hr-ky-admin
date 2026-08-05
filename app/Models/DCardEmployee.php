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
}
