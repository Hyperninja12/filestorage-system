<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
        'color',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FileFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FileFolder::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(InventoryFile::class, 'folder_id');
    }
}
