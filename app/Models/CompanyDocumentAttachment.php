<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDocumentAttachment extends Model
{
    use HasFactory;

    protected $table = 'company_document_attachments';

    protected $fillable = [
        'company_document_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'sort_order',
    ];

    protected $casts = [
        'company_document_id' => 'integer',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'file_url',
        'is_pdf',
        'is_image',
    ];

    public function document()
    {
        return $this->belongsTo(CompanyDocument::class, 'company_document_id');
    }

    public function getFileUrlAttribute(): string
    {
        if (!$this->file_path) {
            return '';
        }
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        return asset($this->file_path);
    }

    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_type ?? '') === 'pdf'
            || str_ends_with(strtolower($this->file_path ?? ''), '.pdf');
    }

    public function getIsImageAttribute(): bool
    {
        $ext = strtolower(pathinfo($this->file_path ?? '', PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
    }
}
