<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRecord extends Model
{
    protected $fillable = ['import_batch_id', 'row_no_in_batch', 'row_data', 'image_paths'];

    protected $casts = [
        'row_data' => 'array',
        'image_paths' => 'array',
    ];

    /** Maximum nga numero sa images kada record (2 lang aron dako ang space sa print). */
    public const MAX_IMAGES = 2;

    /** Kuhaa tanang image paths (1–2). Return array sa paths, pwede walay sulod. */
    public function getImagePaths(): array
    {
        $paths = $this->image_paths ?? [];
        if (! is_array($paths)) {
            return [];
        }
        return array_slice(array_values($paths), 0, self::MAX_IMAGES);
    }

    /**
     * Mutator: i-sanitize ang row_data ngadto sa valid UTF-8 sa wala pa i-save aron dili ma-fail ang JSON encoding.
     */
    protected function setRowDataAttribute(mixed $value): void
    {
        $this->attributes['row_data'] = json_encode($this->sanitizeRowDataForJson($value));
    }

    /**
     * Encode row_data for raw insert (same sanitization as the mutator; used for fast bulk import).
     *
     * @param  array<string, mixed>  $value
     */
    public static function encodeRowDataForDatabase(array $value): string
    {
        $model = new static;

        return json_encode($model->sanitizeRowDataForJson($value), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Sigurohon nga valid UTF-8 ang tanang string sa row_data (recursive); number ug null biyai lang.
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
     * Kuhaa ang value sa column gamit ang key; exact match una, dayon case-insensitive, dayon trim/BOM-normalized match.
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

    /** Tangtanga ang BOM, trim, ug normalize aron mag-match ang "Account Code", "Qty.", "Po No." sa atong column names. */
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

    /** Display number that restarts per import batch; fallback to DB id for legacy rows. */
    public function getDisplayNumber(): int
    {
        return $this->row_no_in_batch ?? $this->id;
    }
}
