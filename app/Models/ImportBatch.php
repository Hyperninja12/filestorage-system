<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = ['filename', 'headers'];

    protected $casts = [
        'headers' => 'array',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(ImportRecord::class);
    }
}
