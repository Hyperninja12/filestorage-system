# Functions Reference – Every Function Used in the Data Import System

This document lists every function (method) in the main application code, with location, parameters, return type, and a short explanation. Use it when you need to understand or change a specific piece of logic.

---

## Table of contents

1. [ImportController](#1-importcontroller)
2. [RecordController](#2-recordcontroller)
3. [ImportRecord (Model)](#3-importrecord-model)
4. [ImportBatch (Model)](#4-importbatch-model)
5. [ClearImportDataCommand](#5-clearimportdatacommand)

---

## 1. ImportController

**File:** `app/Http/Controllers/ImportController.php`

---

### `create()`

- **Returns:** `Illuminate\View\View`
- **Explanation:** Shows the import page. Returns the Blade view `imports.create` (the form where the user selects a CSV or Excel file and clicks Import). No parameters; no database or file access.

---

### `store(Request $request)`

- **Parameters:** `$request` – HTTP request containing the uploaded file under the key `file`.
- **Returns:** `Illuminate\Http\RedirectResponse`
- **Explanation:** Handles the import form submission. Validates the file (required, type csv/xlsx/xls, max 10240 KB), then:
  - Determines extension and calls `parseCsv($file)` or `parseExcel($file)` to get `['headers' => [...], 'rows' => [...]]`.
  - If there are no data rows, redirects back with an error message.
  - Creates one `ImportBatch` with the original filename and the 17 canonical column names.
  - For each parsed row: skips it if `isRowEmpty($row)`; otherwise normalizes it with `normalizeRowToCanonical($row, $canonical)`, cleans with `ensureUtf8Recursive()`, and creates an `ImportRecord` linked to the batch with that `row_data`.
  - Redirects to the records index with a success message (e.g. “X records imported successfully.”).

---

### `isRowEmpty(array $row): bool`

- **Parameters:** `$row` – Associative array of column name => cell value (e.g. one row from the file).
- **Returns:** `true` if every value in the row is null or empty/whitespace; `false` otherwise.
- **Explanation:** Used to skip blank rows (e.g. empty line after the header in a CICTMO PAR CSV). Loops over each value; if any value is non-null and has non-empty content after trim, returns `false`. Otherwise returns `true`.

---

### `getHeaderAliases(): array`

- **Returns:** Array mapping canonical column names to alternate header names that should be treated as that column.
- **Explanation:** Lets the importer match file headers that don’t exactly match the 17 column names. Example: `'Description' => ['descriptions', 'desc']` so that a column named “Descriptions” or “Desc” in the file is mapped to the canonical “Description” column. Add more entries here to support other file formats.

---

### `normalizeRowToCanonical(array $row, array $canonical): array`

- **Parameters:**  
  - `$row` – One data row from the file (keys = file headers, values = cell values).  
  - `$canonical` – The 17 canonical column names (e.g. `RecordController::TABLE_COLUMNS`).
- **Returns:** One row with the same 17 keys as `$canonical` and values taken from `$row` (by name match or by position), with one extra rule for Description/Subcategory.
- **Explanation:** Maps the file’s columns into the fixed 17-column shape. For each canonical column it: (1) tries to find a matching key in `$row` using case-insensitive comparison and `ImportRecord::normalizeColumnKey()` (and header aliases for Description); (2) if no match, uses the value at the same index in `$row`. After that, if “Description” is empty but “Subcategory” has text, it copies Subcategory into Description (for CICTMO-style files). Used only during import.

---

### `parseCsv($file): array`

- **Parameters:** `$file` – Uploaded file (e.g. `Illuminate\Http\UploadedFile`) for a CSV.
- **Returns:** `['headers' => string[], 'rows' => array[]]`. Each row is an associative array keyed by header name.
- **Explanation:** Reads the CSV using League CSV `Reader::createFromPath($file->getRealPath())`. First row is treated as headers: each cell is cleaned with `normalizeHeaderCell()`, then `makeHeadersUnique()` so duplicate names get suffixes (e.g. `Remarks_2`). Rows 2 onward become data; each row is built by pairing header names with cell values. Empty rows are skipped via `isRowEmpty()`. Multi-line quoted fields are handled by League CSV.

---

### `parseExcel($file): array`

- **Parameters:** `$file` – Uploaded file for an Excel workbook (.xlsx or .xls).
- **Returns:** Same shape as `parseCsv`: `['headers' => string[], 'rows' => array[]]`.
- **Explanation:** Loads the file with PhpSpreadsheet `IOFactory::load()`, uses the first sheet, and converts it to an array with `toArray()`. First row is normalized and made unique like in CSV. Each data row’s cells are converted to strings via `excelCellToString()` (handles numbers, dates, etc.). Empty rows are skipped. Returns the same structure as `parseCsv` so `store()` can treat both formats the same.

---

### `normalizeHeaderCell(string $cell): string`

- **Parameters:** `$cell` – Raw header cell value (may have spaces or BOM).
- **Returns:** Trimmed string with UTF-8 BOM removed if present.
- **Explanation:** Trims the cell and, if it starts with the UTF-8 BOM (`\xEF\xBB\xBF`), strips those three bytes. Ensures header names match when comparing (e.g. “Account Code” vs “\xEF\xBB\xBFAccount Code”).

---

### `makeHeadersUnique(array $rawHeaders): array`

- **Parameters:** `$rawHeaders` – Array of header names from the first row (may have duplicates or empty strings).
- **Returns:** Array of the same length; duplicate names get a numeric suffix (`Name`, `Name_2`, `Name_3`, …); empty names become `column_0`, `column_1`, etc.
- **Explanation:** Ensures the first row has unique keys so each column can be identified. Empty or null headers are replaced with `column_` plus index. Used for both CSV and Excel parsing.

---

### `excelCellToString(mixed $cell): string`

- **Parameters:** `$cell` – A cell value from PhpSpreadsheet (string, number, date, etc.).
- **Returns:** String representation suitable for storing in `row_data`.
- **Explanation:** Converts Excel cell values to strings: null/empty → `''`, string → unchanged, int/float → string cast, `DateTimeInterface` → `'Y-m-d'`, anything else → string cast. Ensures we don’t store objects in the JSON.

---

### `ensureUtf8Recursive(mixed $value): mixed`

- **Parameters:** `$value` – Can be an array (of arrays/strings) or a string.
- **Returns:** Same structure; every string is cleaned to valid UTF-8 (invalid bytes stripped via `iconv`); arrays are processed recursively.
- **Explanation:** Prevents “Malformed UTF-8” when encoding to JSON. Uses `iconv('UTF-8', 'UTF-8//IGNORE', $value)` on strings; recurses into arrays. Numbers and null are left as-is. Called on each normalized row before saving to the database.

---

## 2. RecordController

**File:** `app/Http/Controllers/RecordController.php`

---

### `TABLE_COLUMNS` (constant)

- **Type:** `array` (public const).
- **Explanation:** The 17 fixed column names in display order. Single source of truth for: import mapping, table headers, show/edit forms, and update logic. Used by `ImportController` and all RecordController methods. Change this array to add/remove/reorder columns app-wide.

---

### `index(Request $request)`

- **Parameters:** `$request` – GET request; may contain `search`, `column`, and `value` for filtering.
- **Returns:** `Illuminate\View\View` (Blade view `records.index` with variables `records`, `headers`).
- **Explanation:** Records list page. Builds a query: `ImportRecord::with('importBatch')`, latest first. If `search` is present, runs a raw SQL query using SQLite’s `json_each(import_records.row_data)` and `LIKE` on values to get matching record IDs, then restricts the query to those IDs. If both `column` and `value` are present, uses `json_extract(row_data, '$.Column Name')` and `LIKE` to filter by that column. Paginates with 15 per page and preserves query string. Passes `$records` (paginator) and `$headers` (= `TABLE_COLUMNS`) to the view.

---

### `show(ImportRecord $record)`

- **Parameters:** `$record` – Resolved by route model binding from the URL (e.g. `/records/123`).
- **Returns:** `Illuminate\View\View` (`records.show` with `record`, `columns`).
- **Explanation:** View-one-record page. Loads the record’s `importBatch` relation, passes the record and `TABLE_COLUMNS` as `$columns` to the view so it can display every field (and the attached image if any).

---

### `edit(ImportRecord $record)`

- **Parameters:** `$record` – The record to edit (from route).
- **Returns:** `Illuminate\View\View` (`records.edit` with `record`, `columns`).
- **Explanation:** Edit form page. Loads `importBatch`, passes the record and the 17 column names so the view can render one input per column with current values from `$record->getColumn($col)`.

---

### `update(Request $request, ImportRecord $record)`

- **Parameters:** `$request` – Form data (inputs named with the 17 column names). `$record` – The record being updated.
- **Returns:** `Illuminate\Http\RedirectResponse` to the records index with success message.
- **Explanation:** Saves edits. Reads current `$record->row_data`. For each name in `TABLE_COLUMNS`, gets the value from the request; if present, finds the matching key in `row_data` using case-insensitive comparison and `ImportRecord::normalizeColumnKey()`, then updates that key (or adds it). Saves `row_data` on the model (the model’s mutator sanitizes UTF-8 again). Does not validate individual columns; any string is accepted.

---

### `destroy(ImportRecord $record)`

- **Parameters:** `$record` – The record to delete.
- **Returns:** `Illuminate\Http\RedirectResponse` to the records index with success message.
- **Explanation:** Deletes one record. If the record has an `image_path`, deletes that file from the `public` disk (e.g. `storage/app/public/record-images/...`). Then deletes the `ImportRecord` row. Used when the user clicks Delete on the list or on the show page.

---

### `attachImage(Request $request, ImportRecord $record)`

- **Parameters:** `$request` – Must contain an uploaded file under the key `image`. `$record` – The record to attach the image to.
- **Returns:** `Illuminate\Http\RedirectResponse` back to the previous page with success message.
- **Explanation:** Validates `image` (required, type image, mimes jpeg/png/jpg/gif/webp, max 5120 KB). If the record already had an image, deletes the old file from the public disk. Stores the new file under `record-images/` on the `public` disk and updates the record’s `image_path`. One image per record; attaching a new one replaces the old.

---

### `image(ImportRecord $record)`

- **Parameters:** `$record` – The record whose image is requested.
- **Returns:** `Symfony\Component\HttpFoundation\BinaryFileResponse` (the file) or 404.
- **Explanation:** Serves the attached image file. If the record has no `image_path` or the file does not exist on disk, aborts with 404. Otherwise returns the file with `response()->file()` so the browser can display it (e.g. in the preview modal or on the show page). Requires `php artisan storage:link` so `public/storage` points to the right place.

---

## 3. ImportRecord (Model)

**File:** `app/Models/ImportRecord.php`

---

### `setRowDataAttribute(mixed $value): void`

- **Parameters:** `$value` – The value being set on the `row_data` attribute (array or similar).
- **Returns:** Nothing (mutator).
- **Explanation:** Laravel mutator for `row_data`. Before saving, it runs `sanitizeRowDataForJson($value)` and then `json_encode()` so that only valid UTF-8 strings are stored. This avoids “Malformed UTF-8” errors when saving or re-saving records (e.g. after edit). The result is stored in `$this->attributes['row_data']`.

---

### `sanitizeRowDataForJson(mixed $value): mixed`

- **Parameters:** `$value` – Can be an array (nested) or a string or number/null.
- **Returns:** Same structure with every string replaced by a UTF-8-safe string (invalid bytes removed via `iconv`).
- **Explanation:** Recursively walks the value: arrays are mapped through this function again; strings are cleaned with `iconv('UTF-8', 'UTF-8//IGNORE', $value)` (or `''` on failure); numbers and null are returned as-is. Used only inside the mutator.

---

### `importBatch(): BelongsTo`

- **Returns:** `Illuminate\Database\Eloquent\Relations\BelongsTo` (relationship to `ImportBatch`).
- **Explanation:** Eloquent relationship: each record belongs to one import batch. Used for `$record->importBatch` and eager loading in the list/show/edit views.

---

### `getColumn(string $key): mixed`

- **Parameters:** `$key` – Column name (e.g. “Account Code”, “Qty”, “Description”).
- **Returns:** The value for that column in `row_data`, or `null` if not found.
- **Explanation:** Resolves the value for a column in a flexible way: (1) exact key match in `row_data`; (2) case-insensitive key match; (3) match after normalizing both the requested key and each stored key with `normalizeColumnKey()`. So “Qty.” and “Qty” both work. Used everywhere we display or edit a single column (index table, show page, edit form).

---

### `normalizeColumnKey(string $key): string`

- **Parameters:** `$key` – A column name (e.g. from the file or from the form).
- **Returns:** Trimmed string with BOM and trailing period removed.
- **Explanation:** Static helper. Trims the key, removes the UTF-8 BOM if present, then trims again and removes a trailing dot (so “Qty.” and “Po No.” match “Qty” and “PO No.”). Used when matching file headers to canonical names and when matching form input to stored keys.

---

### `setColumn(string $key, mixed $value): void`

- **Parameters:** `$key` – Column name. `$value` – New value for that column.
- **Returns:** Nothing.
- **Explanation:** Sets one key in `row_data` to the given value and assigns the updated array back to `row_data` (so the mutator will run on save). Used when you need to change a single column programmatically; the edit form usually goes through `RecordController::update()` instead and replaces the whole `row_data` object.

---

## 4. ImportBatch (Model)

**File:** `app/Models/ImportBatch.php`

---

### `records(): HasMany`

- **Returns:** `Illuminate\Database\Eloquent\Relations\HasMany` (relationship to `ImportRecord`).
- **Explanation:** Eloquent relationship: one batch has many records. Used when creating records during import (`$batch->records()->create([...])`) and when eager loading (`ImportRecord::with('importBatch')`). No other methods in this model; it only holds filename, headers, and this relation.

---

## 5. ClearImportDataCommand

**File:** `app/Console/Commands/ClearImportDataCommand.php`

---

### `handle(): int`

- **Parameters:** None (options come from the command: `--force` to skip confirmation).
- **Returns:** `int` – Command exit code (`self::SUCCESS`).
- **Explanation:** Artisan command run with `php artisan imports:clear`. Unless `--force` is used, asks for confirmation (“Delete ALL import records, batches, and attached images?”). Counts records and batches, then: for each `ImportRecord`, deletes its image file from the `public` disk if `image_path` is set, then deletes the record; then deletes all `ImportBatch` rows. Outputs how many records and batches were deleted. Use this to reset all import data.

---

## Quick lookup by task

| Task | Function(s) to look at |
|------|------------------------|
| Show import form | `ImportController::create` |
| Process uploaded file | `ImportController::store`, `parseCsv` / `parseExcel`, `normalizeRowToCanonical`, `ensureUtf8Recursive` |
| Skip blank rows | `ImportController::isRowEmpty` |
| Map “Descriptions” → “Description” | `ImportController::getHeaderAliases`, `normalizeRowToCanonical` |
| Map “Qty.” / “Po No.” | `ImportRecord::normalizeColumnKey` (used in import and in getColumn) |
| Fix duplicate CSV headers | `ImportController::makeHeadersUnique` |
| Fix UTF-8 / JSON errors | `ImportController::ensureUtf8Recursive`, `ImportRecord::setRowDataAttribute`, `sanitizeRowDataForJson` |
| List records + search/filter | `RecordController::index` |
| View one record | `RecordController::show`, `ImportRecord::getColumn` in view |
| Edit form + save | `RecordController::edit`, `RecordController::update`, `ImportRecord::getColumn` |
| Delete record | `RecordController::destroy` |
| Attach / serve image | `RecordController::attachImage`, `RecordController::image` |
| Get value for a column | `ImportRecord::getColumn` |
| Clear all import data | `ClearImportDataCommand::handle` |

---

*This reference covers the functions in the main application code. For overall flow and file locations, see `docs/CODE_DOCUMENTATION.md`.*
