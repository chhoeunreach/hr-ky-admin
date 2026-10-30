<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyPayrollRecord extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array', 'columns' => 'array', 'rows' => 'array', 'summary' => 'array',
    ];
}
