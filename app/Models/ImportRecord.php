<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRecord extends Model
{
    protected $fillable = ['import_batch_id', 'row_data', 'image_path'];

    protected $casts = [
        'row_data' => 'array',
    ];

    /**
     * Mutator: sanitize row_data to valid UTF-8 before saving so JSON encoding never
     * fails with "Malformed UTF-8" (e.g. when editing records or re-saving).
     */
    protected function setRowDataAttribute(mixed $value): void
    {
        $this->attributes['row_data'] = json_encode($this->sanitizeRowDataForJson($value));
    }

    /**
     * Recursively ensure strings in row_data are valid UTF-8; leave numbers/null as-is.
     */
    private function sanitizeRowDataForJson(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->sanitizeRowDataForJson($v), $value);
        }
        if (is_string($value)) {
            $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            return $clean !== false ? $clean : '';
        }
        return $value;
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    /**
     * Get value for a column by key; exact match, then case-insensitive, then trim/BOM-normalized match.
     */
    public function getColumn(string $key): mixed
    {
        $data = $this->row_data ?? [];
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }
        $keyNorm = self::normalizeColumnKey($key);
        foreach ($data as $k => $v) {
            if (strcasecmp($k, $key) === 0) {
                return $v;
            }
            if (strcasecmp(self::normalizeColumnKey($k), $keyNorm) === 0) {
                return $v;
            }
        }
        return null;
    }

    /** Strip BOM, trim, and normalize so "Account Code", "Qty.", "Po No." match our column names. */
    public static function normalizeColumnKey(string $key): string
    {
        $key = trim($key);
        if (str_starts_with($key, "\xEF\xBB\xBF")) {
            $key = substr($key, 3);
        }
        $key = trim($key);
        if (str_ends_with($key, '.')) {
            $key = rtrim($key, '.');
        }
        return trim($key);
    }

    public function setColumn(string $key, mixed $value): void
    {
        $data = $this->row_data ?? [];
        $data[$key] = $value;
        $this->row_data = $data;
    }
}
