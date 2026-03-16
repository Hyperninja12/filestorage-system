<?php

namespace App\Http\Controllers;

use App\Models\ImportRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecordController extends Controller
{
    /** Mga column sa table (fixed) para sa PPE report, sunod sa display. Gamit sa index, show, ug edit views. */
    public const TABLE_COLUMNS = [
        'Account Code',
        'Fund',
        'Category',
        'Subcategory',
        'Description',
        'Date of Purchase',
        'Property No.',
        'PO No.',
        'Unit',
        'Qty',
        'Unit Value',
        'On Hand Count',
        'On Hand Value',
        'Person Responsible',
        'Office',
        'Floor',
        'Area Location',
        'Additional Information',
        'Remarks',
    ];

    public function index(Request $request)
    {
        $query = ImportRecord::with('importBatch');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $ids = DB::select(
                'SELECT DISTINCT import_records.id FROM import_records CROSS JOIN json_each(import_records.row_data) AS je WHERE je.value LIKE ?',
                [$search]
            );
            $idList = array_map(fn ($row) => is_object($row) ? $row->id : $row['id'], $ids);
            if (empty($idList)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $idList);
            }
        }

        if ($request->filled('floor')) {
            $floor = '%' . $request->floor . '%';
            $query->whereRaw('json_extract(row_data, ?) LIKE ?', ['$."Floor"', $floor]);
        }

        if ($request->filled('person_responsible')) {
            $pr = '%' . $request->person_responsible . '%';
            $query->whereRaw('json_extract(row_data, ?) LIKE ?', ['$."Person Responsible"', $pr]);
        }

        $records = $query->oldest()->paginate(15)->withQueryString();
        $headers = self::TABLE_COLUMNS;
        return view('records.index', compact('records', 'headers'));
    }

    public function show(ImportRecord $record)
    {
        $record->load('importBatch');
        $columns = self::TABLE_COLUMNS;
        return view('records.show', compact('record', 'columns'));
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
        $data = $record->row_data ?? [];
        foreach (self::TABLE_COLUMNS as $col) {
            $underscoreCol = str_replace(' ', '_', $col);
            $hasField = $request->has($col) || $request->has($underscoreCol);
            if (! $hasField) {
                continue;
            }
            $value = $request->input($col) ?? $request->input($underscoreCol);
            $value = $value === null ? '' : trim((string) $value);
            $found = false;
            foreach (array_keys($data) as $storedKey) {
                if (strcasecmp(ImportRecord::normalizeColumnKey($storedKey), ImportRecord::normalizeColumnKey($col)) === 0) {
                    $data[$storedKey] = $value;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $data[$col] = $value;
            }
        }
        $record->update(['row_data' => $data]);
        return redirect()->route('records.index')->with('success', 'Record updated.');
    }

    public function destroy(ImportRecord $record)
    {
        foreach ($record->getImagePaths() as $path) {
            Storage::disk('public')->delete($path);
        }
        $record->delete();
        return redirect()->route('records.index')->with('success', 'Record deleted.');
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
        if (! isset($paths[$idx])) {
            abort(404);
        }
        $path = Storage::disk('public')->path($paths[$idx]);
        if (! file_exists($path)) {
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
            'po_no' => $record->getColumn('PO No.') ?: '—',
            'unit_value' => $unitValue !== null && $unitValue !== '' ? $this->formatNumberForPrint($unitValue, true) : '—',
            'qty' => $this->formatQtyForPrint($qty, $unit),
            'person_responsible' => $record->getColumn('Person Responsible') ?: '—',
            'office' => $record->getColumn('Office') ?: '—',
            'area_location' => $record->getColumn('Area Location') ?: $record->getColumn('Floor') ?: '—',
            'section' => $record->getColumn('Section') ?? $record->getColumn('SECTION') ?? '—',
            'additional_information' => $record->getColumn('Additional Information') ?: '—',
            'remarks' => $record->getColumn('Remarks') ?: '—',
            'notes' => $record->getColumn('Notes') ?? '—',
        ];

        $imageUrls = [];
        foreach ($record->getImagePaths() as $i => $_) {
            $imageUrls[] = route('records.image', [$record, $i]);
        }

        return view('records.print', compact('record', 'data', 'imageUrls'));
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

    private function formatNumberForPrint(mixed $value, bool $withDecimals): string
    {
        $str = trim((string) $value);
        $str = str_replace(',', '', $str);
        $num = is_numeric($str) ? (float) $str : 0;
        return $withDecimals ? number_format($num, 2) : number_format((int) round($num));
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
