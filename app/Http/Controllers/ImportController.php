<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RecordController;
use App\Models\ImportBatch;
use App\Models\ImportRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    /** Chunk size for bulk insert (SQLite variable limit ~999; 5 columns × 150 = 750). */
    private const IMPORT_INSERT_CHUNK = 150;

    public function create()
    {
        return view('imports.create');
    }

    /**
     * Import file: unang row = headers, sunod nga rows = data. Normalize kada row sa 17 canonical column names aron makita sa records table.
     */
    public function store(Request $request)
    {
        @set_time_limit(0);

        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            ]);

            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            $parsed = $extension === 'csv'
                ? $this->parseCsv($file)
                : $this->parseExcel($file);

            if (empty($parsed['rows'])) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'No data found in the file.',
                    ], 422);
                }

                return back()->with('error', 'No data found in the file.');
            }

            $canonical = RecordController::TABLE_COLUMNS;

            $created = 0;
            DB::transaction(function () use ($file, $parsed, $canonical, &$created): void {
                $batch = ImportBatch::create([
                    'filename' => $file->getClientOriginalName(),
                    'headers' => $canonical,
                ]);

                $now = now();
                $emptyImages = json_encode([], JSON_UNESCAPED_UNICODE);
                $buffer = [];
                $rowNo = 0;

                foreach ($parsed['rows'] as $row) {
                    if ($this->isRowEmpty($row)) {
                        continue;
                    }
                    $normalized = $this->normalizeRowToCanonical($row, $canonical);
                    $rowNo++;

                    $buffer[] = [
                        'import_batch_id' => $batch->id,
                        'row_no_in_batch' => $rowNo,
                        'row_data' => ImportRecord::encodeRowDataForDatabase($normalized),
                        'image_paths' => $emptyImages,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $created++;

                    if (count($buffer) >= self::IMPORT_INSERT_CHUNK) {
                        ImportRecord::insert($buffer);
                        $buffer = [];
                    }
                }

                if ($buffer !== []) {
                    ImportRecord::insert($buffer);
                }
            });

            $message = $created . ' records imported successfully.';

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => $message,
                    'created' => $created,
                    'redirect' => route('records.index'),
                ]);
            }

            return redirect()->route('records.index')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Import failed on the server. Check storage/logs/laravel.log for details.',
                ], 500);
            }

            return back()->with('error', 'Import failed. Check storage/logs/laravel.log for details.');
        }
    }

    /** Laktawi ang blank rows (pananglitan walay sulod nga line human sa header). */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v === null) {
                continue;
            }
            // Strip invisible/unicode chars: zero-width space, non-breaking space, BOM, soft-hyphen, etc.
            $clean = preg_replace('/[\x00-\x1F\x7F\xA0\xAD\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $v);
            if ($clean === null) {
                // Regex engine failed (unlikely) — fall back to basic trim.
                $clean = $v;
            }
            if (trim($clean) !== '') {
                return false;
            }
        }
        return true;
    }

    /** Laing ngalan sa header sa file nga i-map sa canonical column names. */
    private function getHeaderAliases(): array
    {
        return [
            'Account Code' => ['account', 'acct code', 'acct. code'],
            'Fund' => [],
            'Category' => [],
            'Subcategory' => ['sub category', 'sub-cat'],
            'Description' => ['descriptions', 'desc', 'item description'],
            'Date of Purchase' => ['purchase date', 'date purchased', 'date of order', 'order date'],
            'Property No.' => ['property number', 'prop no', 'prop. no.', 'property #'],
            'PO No.' => ['po number', 'po #', 'p.o. no.', 'p.o. number', 'purchase order', 'pono'],
            'Unit' => [],
            'Qty' => ['quantity', 'qty.', 'qnty'],
            'Unit Value' => ['unit cost', 'unit price', 'cost per unit'],
            'On Hand Count' => ['on hand qty', 'oh count', 'stock count'],
            'On Hand Value' => ['oh value', 'total value', 'inventory value'],
            'Person Responsible' => ['person', 'custodian', 'assigned to', 'responsible person'],
            'Office' => [],
            'Area Location' => ['area', 'location', 'site'],
            'Additional Information' => ['additional info', 'add info', 'notes', 'other info'],
            'Remarks' => ['remark', 'comments'],
        ];
    }

    /**
     * I-map ang keys sa file row ngadto sa 17 canonical column names (walay labot case, trim/BOM, trailing dot, ug aliases).
     */
    private function normalizeRowToCanonical(array $row, array $canonical): array
    {
        $normalized = [];
        $aliases = $this->getHeaderAliases();
        foreach ($canonical as $index => $col) {
            $value = null;
            $colNorm = ImportRecord::normalizeColumnKey($col);
            $allowed = array_merge([$colNorm], array_map('strtolower', $aliases[$col] ?? []));
            foreach ($row as $key => $v) {
                $keyNorm = ImportRecord::normalizeColumnKey((string) $key);
                if (strcasecmp($keyNorm, $colNorm) === 0 || in_array(strtolower($keyNorm), $allowed, true)) {
                    $value = $v;
                    break;
                }
            }
            // Ayaw gamita ang value sa index kung walay matching header — aron Floor ug uban nga wala sa file dili makakuha ug sayop nga value (e.g. 1).
            $normalized[$col] = $value;
        }
        // Kung walay Description, gamita ang Subcategory nga fallback.
        $desc = $normalized['Description'] ?? null;
        if (($desc === null || trim((string) $desc) === '') && ! empty(trim((string) ($normalized['Subcategory'] ?? '')))) {
            $normalized['Description'] = $normalized['Subcategory'];
        }

        return $this->normalizeImportedRowValues($normalized);
    }

    /**
     * Align imported values with manual record rules:
     * - Date of Purchase: normalized to Y-m-d when parseable
     * - Unit Value / On Hand Value: parsed to non-negative rounded int
     * - Everything else: trimmed string (or null when empty)
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeImportedRowValues(array $row): array
    {
        foreach ($row as $col => $value) {
            if ($value === null) {
                $row[$col] = null;
                continue;
            }

            $s = trim((string) $value);
            if ($s === '') {
                $row[$col] = null;
                continue;
            }

            if ($col === 'Date of Purchase') {
                $row[$col] = $this->normalizeImportedDate($s);
                continue;
            }

            if ($col === 'Unit Value' || $col === 'On Hand Value') {
                $row[$col] = $this->parseFlexibleMoneyToInt($s) ?? $s;
                continue;
            }

            $row[$col] = $s;
        }

        return $row;
    }

    private function normalizeImportedDate(string $raw): string
    {
        $s = trim($raw);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return $s;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
            $month = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            return $m[3] . '-' . $month . '-' . $day;
        }

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $s, $m)) {
            $month = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            return $m[3] . '-' . $month . '-' . $day;
        }

        $ts = strtotime($s);
        return $ts !== false ? date('Y-m-d', $ts) : $s;
    }

    private function parseFlexibleMoneyToInt(string $raw): ?int
    {
        $s = trim($raw);
        if ($s === '') {
            return null;
        }
        $s = str_replace(['₱', "\u{00A0}"], '', $s);
        $s = trim($s);
        $s = str_replace(',', '', $s);
        $s = preg_replace('/\s+/', '', $s) ?? $s;
        if ($s === '' || ! is_numeric($s)) {
            return null;
        }
        $num = (float) $s;
        if ($num < 0) {
            return null;
        }

        return (int) round($num);
    }

    /**
     * CSV: Scan rows to find the real header row (skips metadata rows at the top).
     * Row after the detected header row pataas = data.
     */
    private function parseCsv($file): array
    {
        $path = $file->getRealPath();
        if ($path === false) {
            return ['headers' => [], 'rows' => []];
        }
        $utf8 = $this->fileContentsAsUtf8($path);
        $reader = Reader::createFromString($utf8);
        $allRows = array_values(iterator_to_array($reader->getRecords()));
        if (empty($allRows)) {
            return ['headers' => [], 'rows' => []];
        }
        $headerIdx = $this->findHeaderRowIndex($allRows);
        $rawHeaders = array_map(fn ($c) => $this->normalizeHeaderCell((string) $c), array_values($allRows[$headerIdx]));
        $headers = $this->makeHeadersUnique($rawHeaders);
        $rows = [];
        for ($i = $headerIdx + 1; $i < count($allRows); $i++) {
            $row = [];
            $values = array_values($allRows[$i]);
            foreach ($headers as $j => $key) {
                $row[$key] = $values[$j] ?? '';
            }
            if (! $this->isRowEmpty($row)) {
                $rows[] = $row;
            }
        }
        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Excel: Scan rows to find the real header row (skips metadata rows at the top).
     * Row after the detected header row pataas = data.
     */
    private function parseExcel($file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        // Re-index rows as 0-based array so findHeaderRowIndex() works correctly.
        $rows = array_values($sheet->toArray(null, true, true, true));
        if (empty($rows)) {
            return ['headers' => [], 'rows' => []];
        }
        $headerIdx = $this->findHeaderRowIndex($rows);
        $rawHeaders = array_values($rows[$headerIdx]);
        $rawHeaders = array_map(fn ($h) => $this->normalizeHeaderCell((string) $h) ?: null, $rawHeaders);
        $headers = $this->makeHeadersUnique($rawHeaders);
        $result = [];
        for ($i = $headerIdx + 1; $i < count($rows); $i++) {
            $cells = array_values($rows[$i]);
            $row = [];
            foreach ($headers as $j => $key) {
                $row[$key] = $this->excelCellToString($cells[$j] ?? null);
            }
            if (! $this->isRowEmpty($row)) {
                $result[] = $row;
            }
        }
        return ['headers' => $headers, 'rows' => $result];
    }

    /**
     * Scan through rows and return the index of the first row that contains
     * at least 2 recognisable canonical column names (or their aliases).
     * Falls back to 0 if no such row is found (original behaviour).
     */
    private function findHeaderRowIndex(array $rows): int
    {
        $canonical = RecordController::TABLE_COLUMNS;
        $aliases   = $this->getHeaderAliases();

        // Build a flat lookup of every recognised name (canonical + all aliases) in lowercase.
        $knownNames = [];
        foreach ($canonical as $col) {
            $knownNames[] = strtolower(ImportRecord::normalizeColumnKey($col));
            foreach ($aliases[$col] ?? [] as $alias) {
                $knownNames[] = strtolower(ImportRecord::normalizeColumnKey($alias));
            }
        }
        $knownNames = array_unique($knownNames);

        foreach ($rows as $idx => $row) {
            $matches = 0;
            foreach ($row as $cell) {
                $norm = strtolower(ImportRecord::normalizeColumnKey((string) $cell));
                if ($norm !== '' && in_array($norm, $knownNames, true)) {
                    $matches++;
                    if ($matches >= 2) {
                        return $idx;
                    }
                }
            }
        }

        return 0; // fallback: assume first row is headers
    }

    /** Trim ug tangtangon ang UTF-8 BOM sa header cell aron mag-match ang keys sa display. */
    private function normalizeHeaderCell(string $cell): string
    {
        $cell = trim($cell);
        if (str_starts_with($cell, "\xEF\xBB\xBF")) {
            $cell = substr($cell, 3);
        }
        return trim($cell);
    }

    /**
     * Read CSV as bytes and normalize to UTF-8 so League CSV and JSON storage do not fail on Windows-1252 exports.
     */
    private function fileContentsAsUtf8(string $path): string
    {
        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return '';
        }
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        if (function_exists('mb_check_encoding') && mb_check_encoding($raw, 'UTF-8')) {
            return $raw;
        }
        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $raw);

        return $converted !== false ? $converted : $raw;
    }

    private function makeHeadersUnique(array $rawHeaders): array
    {
        $seen = [];
        $result = [];
        foreach ($rawHeaders as $i => $name) {
            $name = ($name !== null && trim((string) $name) !== '') ? trim((string) $name) : 'column_' . $i;
            if (! isset($seen[$name])) {
                $seen[$name] = 0;
            }
            $seen[$name]++;
            $result[] = $seen[$name] === 1 ? $name : $name . '_' . $seen[$name];
        }
        return $result;
    }

    private function excelCellToString(mixed $cell): string
    {
        if ($cell === null || $cell === '') {
            return '';
        }
        if (is_string($cell)) {
            return $cell;
        }
        if (is_int($cell) || is_float($cell)) {
            return (string) $cell;
        }
        if ($cell instanceof \DateTimeInterface) {
            return $cell->format('Y-m-d');
        }
        return (string) $cell;
    }
}
