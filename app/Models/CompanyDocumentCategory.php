<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDocumentCategory extends Model
{
    use HasFactory;

    protected $table = 'company_document_categories';

    protected $fillable = [
        'code',
        'name_km',
        'name_en',
        'icon',
        'color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'km' ? $this->name_km : ($this->name_en ?: $this->name_km);
    }

    public function documents()
    {
        return $this->hasMany(CompanyDocument::class, 'category_code', 'code');
    }
}
