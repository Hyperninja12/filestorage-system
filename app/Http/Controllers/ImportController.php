<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RecordController;
use App\Models\ImportBatch;
use App\Models\ImportRecord;
use Illuminate\Http\Request;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function create()
    {
        return view('imports.create');
    }

    /**
     * Import file: unang row = headers, sunod nga rows = data. Normalize kada row sa 17 canonical column names aron makita sa records table.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $parsed = $extension === 'csv'
            ? $this->parseCsv($file)
            : $this->parseExcel($file);

        if (empty($parsed['rows'])) {
            return back()->with('error', 'No data found in the file.');
        }

        $canonical = RecordController::TABLE_COLUMNS;
        $batch = ImportBatch::create([
            'filename' => $file->getClientOriginalName(),
            'headers' => $canonical,
        ]);

        $created = 0;
        foreach ($parsed['rows'] as $row) {
            if ($this->isRowEmpty($row)) {
                continue;
            }
            $normalized = $this->normalizeRowToCanonical($row, $canonical);
            $batch->records()->create([
                'row_data' => $this->ensureUtf8Recursive($normalized),
            ]);
            $created++;
        }

        return redirect()->route('records.index')->with('success', $created . ' records imported successfully.');
    }

    /** Laktawi ang blank rows (pananglitan walay sulod nga line human sa header). */
    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && trim((string) $v) !== '') {
                return false;
            }
        }
        return true;
    }

    /** Laing ngalan sa header sa file nga i-map sa canonical column names. */
    private function getHeaderAliases(): array
    {
        return [
            'Description' => ['descriptions', 'desc'],
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
        return $normalized;
    }

    /**
     * CSV: row 0 = headers, row 1 pataas = data. I-store ang records gamit ang header names nga keys.
     */
    private function parseCsv($file): array
    {
        $reader = Reader::createFromPath($file->getRealPath());
        $allRows = iterator_to_array($reader->getRecords());
        if (empty($allRows)) {
            return ['headers' => [], 'rows' => []];
        }
        $rawHeaders = array_map(fn ($c) => $this->normalizeHeaderCell((string) $c), array_values($allRows[0]));
        $headers = $this->makeHeadersUnique($rawHeaders);
        $rows = [];
        for ($i = 1; $i < count($allRows); $i++) {
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
     * Excel: row 0 = headers, row 1 pataas = data. I-store ang records gamit ang header names nga keys.
     */
    private function parseExcel($file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        if (empty($rows)) {
            return ['headers' => [], 'rows' => []];
        }
        $rawHeaders = array_values($rows[0]);
        $rawHeaders = array_map(fn ($h) => $this->normalizeHeaderCell((string) $h) ?: null, $rawHeaders);
        $headers = $this->makeHeadersUnique($rawHeaders);
        $result = [];
        for ($i = 1; $i < count($rows); $i++) {
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

    /** Trim ug tangtangon ang UTF-8 BOM sa header cell aron mag-match ang keys sa display. */
    private function normalizeHeaderCell(string $cell): string
    {
        $cell = trim($cell);
        if (str_starts_with($cell, "\xEF\xBB\xBF")) {
            $cell = substr($cell, 3);
        }
        return trim($cell);
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

    private function ensureUtf8Recursive(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->ensureUtf8Recursive($v), $value);
        }
        if (is_string($value)) {
            $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            return $clean !== false ? $clean : '';
        }
        return $value;
    }
}
