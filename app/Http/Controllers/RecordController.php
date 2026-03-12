<?php

namespace App\Http\Controllers;

use App\Models\ImportRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RecordController extends Controller
{
    /** Fixed table columns (PPE report) in display order. Used for index, show, and edit views. */
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

        $records = $query->latest()->paginate(15)->withQueryString();
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
     * Update record row_data from form. Matches submitted keys to stored keys (case-insensitive) for the 17 table columns.
     */
    public function update(Request $request, ImportRecord $record)
    {
        $data = $record->row_data ?? [];
        foreach (self::TABLE_COLUMNS as $col) {
            $value = $request->input($col);
            if ($value === null) {
                continue;
            }
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
        if ($record->image_path) {
            Storage::disk('public')->delete($record->image_path);
        }
        $record->delete();
        return redirect()->route('records.index')->with('success', 'Record deleted.');
    }

    public function attachImage(Request $request, ImportRecord $record)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($record->image_path) {
            Storage::disk('public')->delete($record->image_path);
        }

        $path = $request->file('image')->store('record-images', 'public');
        $record->update(['image_path' => $path]);

        return back()->with('success', 'Image attached.');
    }

    public function removeImage(ImportRecord $record)
    {
        if ($record->image_path) {
            Storage::disk('public')->delete($record->image_path);
            $record->update(['image_path' => null]);
        }
        return back()->with('success', 'Image removed.');
    }

    public function image(ImportRecord $record)
    {
        if (!$record->image_path) {
            abort(404);
        }
        $path = Storage::disk('public')->path($record->image_path);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }
}
