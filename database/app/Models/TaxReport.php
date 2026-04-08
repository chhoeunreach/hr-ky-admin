<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxReport extends Model
{
    use HasFactory;

    protected $table = 'tax_reports';

    protected $fillable = [
        'employee_id', 'fiscal_year_id', 'total_basic_salary', 'total_allowance', 'total_ssf_contribution', 'total_ssf_deduction', 'female_discount',
        'other_discount', 'total_payable_tds', 'total_paid_tds', 'total_due_tds','months'
    ];

    public function fiscalYear():BelongsTo
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id', 'id');
    }

    public function employee():BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');
    }

    public function additionalDetail(): HasMany
    {
        return $this->hasMany(TaxReportAdditionalDetail::class,'tax_report_id','id');
    }
    public function bonusDetail(): HasMany
    {
        return $this->hasMany(TaxReportBonusDetail::class,'tax_report_id','id');
    }
    public function tdsDetail(): HasMany
    {
        return $this->hasMany(TaxReportTdsDetail::class,'tax_report_id','id');
    }
    public function componentDetail(): HasMany
    {
        return $this->hasMany(TaxReportComponentDetail::class,'tax_report_id','id');
    }

    public function reportDetail(): HasMany
    {
        return $this->hasMany(TaxReportDetail::class,'tax_report_id','id');
    }
}
