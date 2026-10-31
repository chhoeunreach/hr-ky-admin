<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Warning extends Model
{
    use HasFactory;
    protected $table = 'warnings';
    protected $fillable = [
        'branch_id', 'subject', 'warning_date', 'message', 'status', 'created_by', 'updated_by'
    ];


    const RECORDS_PER_PAGE = 20;

    public static function boot()
    {
        parent::boot();

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                 $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class,'branch_id','id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function warningEmployee()
    {
        return $this->hasMany(WarningEmployee::class,'warning_id','id');
    }
    public function warningDepartment()
    {
        return $this->hasMany(WarningDepartment::class,'warning_id','id');
    }
    public function warningReply()
    {
        return $this->hasMany(WarningResponse::class,'warning_id','id');
    }

    public function getWarningNumberAttribute(): ?string
    {
        return $this->id ? 'WRN-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT) : null;
    }

    public function getPrintableMessageAttribute(): string
    {
        if (!filled($this->message)) {
            return '';
        }
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="UTF-8"><html><body>' . $this->message . '</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        foreach ($xpath->query('//script|//style') as $node) {
            $node->parentNode->removeChild($node);
        }
        foreach ($xpath->query('//br') as $node) {
            $node->parentNode->replaceChild($document->createTextNode("\n"), $node);
        }
        foreach ($xpath->query('//p|//div|//li|//h1|//h2|//h3|//h4|//h5|//h6') as $node) {
            $node->appendChild($document->createTextNode("\n"));
        }
        return trim($document->getElementsByTagName('body')->item(0)?->textContent ?? '');
    }
}
