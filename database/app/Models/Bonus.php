<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;
    protected $table = 'bonuses';

    protected $fillable = [
        'title', 'description', 'value_type', 'value', 'applicable_month', 'is_active',
    ];
}
