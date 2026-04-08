<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\ImportRecord;
use App\Support\CashFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RecordController extends Controller
{
    /** Filename used for the batch that holds manually added records. */
    public const MANUAL_BATCH_FILENAME = 'Manual entries';

    /** Unit Value threshold: PAR = 50,000 and up, ICS = below 50,000. */
    public const PAR_ICS_THRESHOLD = 50000;

    /** Columns that may be left empty when manually adding a record (all others are required on create). */
    public const MANUAL_CREATE_OPTIONAL_COLUMNS = [
        'Subcategory',
        'Area Location',
        'Inventory Item No.',
    ];

    /** Mga column sa table (fixed) para sa PPE report, sunod sa display. Gamit sa index, show, ug edit views. */
    public const TABLE_COLUMNS = [
        'Account Code',
        'Fund',
        'Category',
        'Subcategory',
        'Description',
        'Date of Purchase',
        'Property No.',
        'Inventory Item No.',
        'PO No.',
        'Unit',
        'Qty',
        'Unit Value',
        'On Hand Count',
        'On Hand Value',
        'Person Responsible',
        'Office',
        'Area Location',
        'Additional Information',
        'Remarks',
    ];

    private function buildRecordsQuery(Request $request)
    {
        $query = ImportRecord::with('importBatch');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $ids = DB::select(
                'SELECT DISTINCT import_records.id FROM import_records CROSS JOIN json_each(import_records.row_data) AS je WHERE je.value LIKE ?',
                [$search]
            );
            $idList = array_map(fn($row) => is_object($row) ? $row->id : $row['id'], $ids);
            if (empty($idList)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $idList);
            }
        }

        if ($request->filled('person_responsible')) {
            $pr = '%' . $request->person_responsible . '%';
            $query->whereRaw('json_extract(row_data, ?) LIKE ?', ['$."Person Responsible"', $pr]);
        }

        $type = $request->get('type', 'all');
        if ($type === 'par' || $type === 'ics') {
            $query->whereRaw(self::unitValueRawCondition($type));
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->buildRecordsQuery($request);
        $type = $request->get('type', 'all');

        $records = $query->oldest()->paginate(15)->withQueryString();

        if ($records->currentPage() > $records->lastPage() && $records->lastPage() > 0) {
            return redirect()->to($request->fullUrlWithQuery(['page' => $records->lastPage()]));
        }

        // Stats for the header dashboard
        $totalCount = ImportRecord::count();
        $parCount = ImportRecord::whereRaw(self::unitValueRawCondition('par'))->count();
        $icsCount = ImportRecord::whereRaw(self::unitValueRawCondition('ics'))->count();

        $headers = self::TABLE_COLUMNS;
        return view('records.index', compact('records', 'headers', 'type', 'totalCount', 'parCount', 'icsCount'));
    }

    public function show(ImportRecord $record)
    {
        $record->load('importBatch');
        $columns = self::TABLE_COLUMNS;
        return view('records.show', compact('record', 'columns'));
    }

    public function printList(Request $request)
    {
        $query = $this->buildRecordsQuery($request);
        $type = $request->get('type', 'all');
        $personResponsible = $request->get('person_responsible');

        // Fetch all matching records without pagination for PDF
        $records = $query->oldest()->get();
        $headers = self::TABLE_COLUMNS;

        $pdf = Pdf::loadView('records.print-list', compact('records', 'headers', 'type', 'personResponsible'));

        // Long Bond Paper: 8.5in x 13in in Landscape orientation
        $pdf->setPaper([0, 0, 612, 936], 'landscape');

        $filename = 'Records';
        if ($personResponsible) {
            $filename .= '_' . str_replace(' ', '_', $personResponsible);
        }
        $filename .= '_' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show form to add a new record manually (no import file).
     */
    public function create()
    {
        $columns = self::TABLE_COLUMNS;
        $manualCreateOptionalColumns = self::MANUAL_CREATE_OPTIONAL_COLUMNS;

        return view('records.create', compact('columns', 'manualCreateOptionalColumns'));
    }

    /**
     * Store a new record from the manual-add form. Uses a dedicated "Manual entries" batch.
     */
    public function store(Request $request)
    {
        $batch = ImportBatch::firstOrCreate(
            ['filename' => self::MANUAL_BATCH_FILENAME],
            ['headers' => self::TABLE_COLUMNS]
        );

        $data = $this->validatedRecordData($request, requireAllFieldsForCreate: true);
        $nextRowNo = ((int) ImportRecord::where('import_batch_id', $batch->id)->max('row_no_in_batch')) + 1;

        $record = ImportRecord::create([
            'import_batch_id' => $batch->id,
            'row_no_in_batch' => $nextRowNo,
            'row_data' => $data,
            'image_paths' => [],
        ]);

        return redirect()->route('records.show', $record)->with('success', 'Record added.');
    }

    public function edit(ImportRecord $record)
    {
        $record->load('importBatch');
        $columns = self::TABLE_COLUMNS;
        return view('records.edit', compact('record', 'columns'));
    }

    /**
     * Update ang row_data sa record gikan sa form. I-match ang gipasa nga keys sa stored keys (walay labot sa case).
     * Ang keys nga naay space mahimong underscore sa request (pananglitan "Area_Location"), so susihon pareho.
     * Mudawat ug null/empty: kung wala’y sulod ang field, i-save gihapon nga walay sulod.
     */
    public function update(Request $request, ImportRecord $record)
    {
        $validatedData = $this->validatedRecordData($request);
        $data = $record->row_data ?? [];

        foreach (self::TABLE_COLUMNS as $col) {
            $value = $validatedData[$col] ?? null;
            $found = false;
            foreach (array_keys($data) as $storedKey) {
                if (strcasecmp(ImportRecord::normalizeColumnKey($storedKey), ImportRecord::normalizeColumnKey($col)) === 0) {
                    $data[$storedKey] = $value;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data[$col] = $value;
            }
        }

        $record->update(['row_data' => $data]);
        return redirect()->route('records.index', $this->recordsListQuery($request))
            ->with('success', 'Record updated.');
    }

    public function destroy(Request $request, ImportRecord $record)
    {
        foreach ($record->getImagePaths() as $path) {
            Storage::disk('public')->delete($path);
        }
        $record->delete();
        return redirect()->route('records.index', $this->recordsListQuery($request))
            ->with('success', 'Record deleted.');
    }

    public function attachImage(Request $request, ImportRecord $record)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $paths = $record->getImagePaths();
        if (count($paths) >= ImportRecord::MAX_IMAGES) {
            return back()->with('error', 'Maximum ' . ImportRecord::MAX_IMAGES . ' images allowed. Remove one to add another.');
        }

        $path = $request->file('image')->store('record-images', 'public');
        $paths[] = $path;
        $record->update(['image_paths' => $paths]);

        return back()->with('success', 'Image attached.');
    }

    public function removeImage(ImportRecord $record, int $index = 0)
    {
        $paths = $record->getImagePaths();
        if (array_key_exists($index, $paths)) {
            Storage::disk('public')->delete($paths[$index]);
            array_splice($paths, $index, 1);
            $record->update(['image_paths' => array_values($paths)]);
            return back()->with('success', 'Image removed.');
        }
        return back()->with('error', 'Image not found.');
    }

    public function image(ImportRecord $record, ?int $index = 0)
    {
        $paths = $record->getImagePaths();
        $idx = $index ?? 0;
        if (!isset($paths[$idx])) {
            abort(404);
        }
        $path = Storage::disk('public')->path($paths[$idx]);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }

    /**
     * Print view: template nga ICS (sama sa ICS.docx). Ablihon sa bag-ong window ug i-trigger ang print dialog.
     */
    public function print(ImportRecord $record)
    {
        $record->load('importBatch');

        $datePurchase = $record->getColumn('Date of Purchase');
        $unitValue = $record->getColumn('Unit Value');
        $qty = $record->getColumn('Qty');
        $unit = $record->getColumn('Unit');

        $data = [
            'account_code' => $record->getColumn('Account Code') ?: '—',
            'category' => $record->getColumn('Category') ?: '—',
            'subcategory' => $record->getColumn('Subcategory') ?: '—',
            'description' => $record->getColumn('Description') ?: '—',
            'date_of_purchase' => $datePurchase ? $this->formatDateForPrint($datePurchase) : '—',
            'property_no' => $record->getColumn('Property No.') ?: '—',
            'inventory_item_no' => $record->getColumn('Inventory Item No.') ?: '—',
            'po_no' => $record->getColumn('PO No.') ?: '—',
            'unit_value' => CashFormatter::formatOrPlaceholder($unitValue),
            'on_hand_value' => CashFormatter::formatOrPlaceholder($record->getColumn('On Hand Value')),
            'qty' => $this->formatQtyForPrint($qty, $unit),
            'person_responsible' => $record->getColumn('Person Responsible') ?: '—',
            'office' => $record->getColumn('Office') ?: '—',
            'area_location' => $record->getColumn('Area Location') ?: '—',
            'additional_information' => $record->getColumn('Additional Information') ?: '—',
            'remarks' => $record->getColumn('Remarks') ?: '—',
        ];

        $imageUrls = [];
        foreach ($record->getImagePaths() as $i => $_) {
            $imageUrls[] = route('records.image', [$record, $i]);
        }

        return view('records.print', compact('record', 'data', 'imageUrls'));
    }

    /**
     * Query params to keep when redirecting back to the records list (pagination + filters).
     *
     * @return array<string, mixed>
     */
    private function recordsListQuery(Request $request): array
    {
        $out = [];
        foreach (['page', 'search', 'person_responsible', 'type'] as $key) {
            if ($request->filled($key)) {
                $out[$key] = $request->input($key);
            }
        }

        return $out;
    }

    /**
     * Collect and trim canonical column values from request.
     * Empty values become null for validation.
     *
     * @return array<string, mixed>
     */
    private function rawRecordDataFromRequest(Request $request): array
    {
        $input = $request->all();
        $fingerprintMap = [];
        foreach ($input as $k => $v) {
            $fp = $this->requestKeyFingerprint((string) $k);
            if ($fp !== '' && !array_key_exists($fp, $fingerprintMap)) {
                $fingerprintMap[$fp] = $v;
            }
        }
        $raw = [];
        foreach (self::TABLE_COLUMNS as $col) {
            $value = $request->input($col);
            if ($value === null) {
                // PHP normalizes form keys by replacing spaces/dots with underscores.
                $normalizedKey = preg_replace('/[.\s]+/', '_', $col);
                $value = $normalizedKey !== null && array_key_exists($normalizedKey, $input)
                    ? $input[$normalizedKey]
                    : null;
            }
            if ($value === null) {
                // Last-resort match: ignore spaces, dots, and underscores/case differences.
                $fp = $this->requestKeyFingerprint($col);
                $value = ($fp !== '' && array_key_exists($fp, $fingerprintMap))
                    ? $fingerprintMap[$fp]
                    : null;
            }
            if ($value === null) {
                $raw[$col] = null;
                continue;
            }
            $value = trim((string) $value);
            $raw[$col] = $value === '' ? null : $value;
        }

        return $raw;
    }

    /**
     * Laravel treats "." in validation rule keys as nested paths (e.g. data_get on "PO No." breaks).
     * Escape dots so flat keys like "PO No." and "Property No." validate against $raw correctly.
     */
    private function validationRuleAttribute(string $column): string
    {
        return str_replace('.', '\\.', $column);
    }

    private function requestKeyFingerprint(string $key): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', $key) ?? '');
    }

    /**
     * Validate and normalize manual add/edit payload to required data types.
     *
     * - Account Code: 0-00-00-000
     * - Date of Purchase: date
     * - PO No.: 00-00-0000
     * - Unit Value / On Hand Value: non-negative number (commas + decimal allowed, stored as rounded integer); shown as cash (₱x,xxx.xx)
     * - Others: string
     * @param  bool  $requireAllFieldsForCreate  If true (manual add), every column is required except
     *                                           those in {@see self::MANUAL_CREATE_OPTIONAL_COLUMNS}.
     *
     * @return array<string, mixed>
     */
    private function validatedRecordData(Request $request, bool $requireAllFieldsForCreate = false): array
    {
        $raw = $this->rawRecordDataFromRequest($request);

        $optionalCols = self::MANUAL_CREATE_OPTIONAL_COLUMNS;
        $specialCols = ['Account Code', 'Date of Purchase', 'PO No.', 'Unit Value', 'On Hand Value'];

        $rules = [];
        foreach (self::TABLE_COLUMNS as $col) {
            if (in_array($col, $specialCols, true)) {
                continue;
            }
            $isOptional = in_array($col, $optionalCols, true);
            $required = $requireAllFieldsForCreate && !$isOptional;
            $rules[$this->validationRuleAttribute($col)] = $required ? ['required', 'string'] : ['nullable', 'string'];
        }

        $presence = $requireAllFieldsForCreate ? ['required'] : ['nullable'];

        if ($requireAllFieldsForCreate) {
            // Manual create keeps strict formats.
            $rules[$this->validationRuleAttribute('Account Code')] = array_merge($presence, ['string', 'regex:/^\d-\d{2}-\d{2}-\d{3}$/']);
            $rules[$this->validationRuleAttribute('Date of Purchase')] = array_merge($presence, ['date']);
            $rules[$this->validationRuleAttribute('PO No.')] = array_merge($presence, ['string', 'regex:/^\d{2}-\d{2}-\d{4}$/']);
        } else {
            // Edit mode accepts legacy/imported values so partially fixing rows won't be blocked.
            $rules[$this->validationRuleAttribute('Account Code')] = ['nullable', 'string'];
            $rules[$this->validationRuleAttribute('Date of Purchase')] = ['nullable', 'string'];
            $rules[$this->validationRuleAttribute('PO No.')] = ['nullable', 'string'];
        }

        $moneyRule = function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || (is_string($value) && trim($value) === '')) {
                return;
            }
            if ($this->parseFlexibleMoneyToFloat((string) $value) === null) {
                $label = $attribute === 'Unit Value' ? 'Unit Value' : 'On Hand Value';
                $fail("{$label} must be a valid cash amount (e.g. 1,234.00 or ₱1,234).");
            }
        };
        $rules[$this->validationRuleAttribute('Unit Value')] = array_merge($presence, ['string', $moneyRule]);
        $rules[$this->validationRuleAttribute('On Hand Value')] = array_merge($presence, ['string', $moneyRule]);

        $poRuleKey = $this->validationRuleAttribute('PO No.');
        $validated = Validator::make(
            $raw,
            $rules,
            [
                $this->validationRuleAttribute('Account Code') . '.regex' => 'Account Code format must be 0-00-00-000.',
                $poRuleKey . '.regex' => 'PO No. format must be 00-00-0000.',
                $this->validationRuleAttribute('Date of Purchase') . '.date' => 'Date of Purchase must be a valid date.',
            ]
        )->validate();

        $normalized = [];
        foreach (self::TABLE_COLUMNS as $col) {
            $value = $validated[$col] ?? null;
            if ($value === null) {
                $normalized[$col] = null;
                continue;
            }

            if ($col === 'Unit Value' || $col === 'On Hand Value') {
                $s = trim((string) $value);
                if ($s === '') {
                    $normalized[$col] = null;
                    continue;
                }
                $normalized[$col] = $this->parseFlexibleMoneyToFloat($s) ?? 0.0;
                continue;
            }

            $normalized[$col] = trim((string) $value);
        }

        return $normalized;
    }

    /**
     * Parse values like "1,234.56" or "1234" into a non-negative float. Returns null if invalid.
     */
    private function parseFlexibleMoneyToFloat(string $raw): ?float
    {
        $s = trim($raw);
        if ($s === '') {
            return null;
        }
        $s = str_replace(['₱', "\u{00A0}"], '', $s);
        $s = trim($s);
        $s = str_replace(',', '', $s);
        $s = preg_replace('/\s+/', '', $s) ?? $s;
        if ($s === '' || !is_numeric($s)) {
            return null;
        }
        $num = (float) $s;
        if ($num < 0) {
            return null;
        }

        return round($num, 2);
    }

    /**
     * Raw SQL condition for PAR (unit value >= threshold) or ICS (unit value < threshold).
     * Parses "Unit Value" from row_data JSON; treats non-numeric/empty as 0.
     */
    private static function unitValueRawCondition(string $type): string
    {
        $op = $type === 'par' ? '>=' : '<';
        $th = self::PAR_ICS_THRESHOLD;
        $path = "'\$.\"Unit Value\"'";
        // Strip ₱, spaces, and commas before casting to REAL for comparison
        $expr = "CAST(REPLACE(REPLACE(REPLACE(COALESCE(TRIM(CAST(json_extract(row_data, {$path}) AS TEXT)), '0'), '₱', ''), ',', ''), ' ', '') AS REAL)";
        return "({$expr} {$op} {$th})";
    }

    private function formatDateForPrint(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }
        $str = trim((string) $value);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $str, $m)) {
            return $m[3] . '/' . $m[2] . '/' . $m[1];
        }
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT) . '/' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '/' . $m[3];
        }
        return $str;
    }

    private function formatQtyForPrint(mixed $qty, mixed $unit): string
    {
        $q = $qty !== null && $qty !== '' ? trim((string) $qty) : '—';
        $u = $unit !== null && trim((string) $unit) !== '' ? trim((string) $unit) : '';
        if ($q === '—') {
            return '—';
        }
        return $u !== '' ? $q . ' ' . strtoupper($u) : $q;
    }
}
