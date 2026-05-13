<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'original_name',
        'storage_path',
        'file_type',
        'file_extension',
        'file_size',
        'description',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class, 'folder_id');
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIconTypeAttribute(): string
    {
        $ext = strtolower($this->file_extension);

        if (in_array($ext, ['pdf'])) {
            return 'pdf';
        }
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return 'image';
        }
        
        if (in_array($ext, ['doc', 'docx', 'txt', 'rtf'])) {
            return 'document';
        }
        
        if (in_array($ext, ['xls', 'xlsx', 'csv'])) {
            return 'spreadsheet';
        }

        if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return 'archive';
        }

        return 'other';
    }
}
