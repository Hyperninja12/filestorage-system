# Code Documentation – Understanding the Data Import System

This document explains how the code in this project works so you can read, change, or extend it. It assumes you know basic Laravel (routes, controllers, views, models).

---

## 1. Project structure (what lives where)

```
app/
├── Console/Commands/
│   └── ClearImportDataCommand.php   ← Artisan command to delete all import data
├── Http/Controllers/
│   ├── ImportController.php         ← Import page + file upload/parse logic
│   └── RecordController.php         ← Records list, view, edit, delete, image attach
└── Models/
    ├── ImportBatch.php              ← One row per uploaded file
    └── ImportRecord.php              ← One row per data row; holds row_data (JSON) + image_path

database/
├── database.sqlite                  ← SQLite database file
└── migrations/
    ├── *_create_import_batches_table.php
    └── *_create_import_records_table.php

resources/views/
├── layouts/
│   └── app.blade.php                ← Main layout (nav, alerts, @yield('content'))
├── imports/
│   └── create.blade.php             ← Import CSV/Excel form + loading overlay
└── records/
    ├── index.blade.php              ← Records table + search/filter + top scroll bar
    ├── show.blade.php               ← Single record view (all fields, full text)
    └── edit.blade.php               ← Edit form (one input per column)

routes/
└── web.php                          ← All web routes
```

---

## 2. The “17 columns” idea (important)

The app always shows data in **17 fixed columns** (e.g. Account Code, Fund, Category, … Remarks). They are defined in **one place**:

- **`RecordController::TABLE_COLUMNS`** (a constant array in `app/Http/Controllers/RecordController.php`).

That list is used for:

- **Import:** File headers are **mapped** to these names (so “Qty.” → “Qty”, “Descriptions” → “Description”, etc.).
- **Storage:** Each record stores a JSON object whose keys are these 17 names (in `import_records.row_data`).
- **Display:** Index, show, and edit views loop over `TABLE_COLUMNS` to show the same columns in the same order.

So: **one source of truth** for column names; everything else (import, display, edit) follows it.

---

## 3. Routes (what URL does what)

Defined in **`routes/web.php`**:

| Method | URL | Controller method | Purpose |
|--------|-----|-------------------|---------|
| GET | `/` | (closure) | Redirect to records list |
| GET | `/import` | `ImportController@create` | Show import form |
| POST | `/import` | `ImportController@store` | Process uploaded file and create records |
| GET | `/records` | `RecordController@index` | List records (with search/filter) |
| GET | `/records/{record}` | `RecordController@show` | View one record (all data) |
| GET | `/records/{record}/edit` | `RecordController@edit` | Show edit form |
| PUT | `/records/{record}` | `RecordController@update` | Save edited record |
| DELETE | `/records/{record}` | `RecordController@destroy` | Delete record (and its image) |
| POST | `/records/{record}/image` | `RecordController@attachImage` | Upload image for record |
| GET | `/records/{record}/image` | `RecordController@image` | Serve the attached image file |

`{record}` is Laravel route model binding: the ID in the URL is resolved to an `ImportRecord` model.

---

## 4. Database and models

### Tables

- **`import_batches`**  
  One row per import: `id`, `filename`, `headers` (JSON of the 17 column names), `timestamps`.

- **`import_records`**  
  One row per imported data row: `id`, `import_batch_id`, `row_data` (JSON), `image_path` (nullable), `timestamps`.  
  `row_data` is a JSON object like  
  `{"Account Code": "1-07-05-020", "Fund": "GF", "Category": "Office Equipment", ...}`.

### ImportBatch (`app/Models/ImportBatch.php`)

- Fillable: `filename`, `headers`.
- Casts: `headers` → array.
- Relationship: `records()` → has many `ImportRecord`.

### ImportRecord (`app/Models/ImportRecord.php`)

- Fillable: `import_batch_id`, `row_data`, `image_path`.
- Casts: `row_data` → array (so in PHP you get an associative array, not a string).
- **`setRowDataAttribute`**  
  Before saving, it runs `sanitizeRowDataForJson()` so every string in `row_data` is valid UTF-8. That avoids “Malformed UTF-8” when encoding to JSON.
- **`getColumn(string $key)`**  
  Returns the value for a column name. It tries: exact key → case-insensitive key → key after `normalizeColumnKey()` (trim, strip BOM, strip trailing dot). So “Qty.” and “Qty” both work.
- **`normalizeColumnKey(string $key)`**  
  Static. Trims, removes UTF-8 BOM, and removes a trailing period (so “Po No.” matches “PO No.”).
- **`setColumn(string $key, mixed $value)`**  
  Sets one key in `row_data` and updates the model (used when editing).
- Relationship: `importBatch()` → belongs to `ImportBatch`.

---

## 5. Import flow (how a file becomes records)

When the user submits the import form, the request hits **`ImportController@store`**.

1. **Validate**  
   `file` required, type `csv` / `xlsx` / `xls`, max 10240 KB.

2. **Parse**  
   - CSV: **`parseCsv($file)`** uses **League\Csv\Reader**: first row = headers, rest = data rows. Headers are cleaned with `normalizeHeaderCell()` (trim, BOM) and made unique with `makeHeadersUnique()` (duplicates get `_2`, `_3`, …). Empty headers become `column_0`, `column_1`, etc.  
   - Excel: **`parseExcel($file)`** uses **PhpSpreadsheet**: first row = headers, rest = data. Same header logic; cell values are normalized with `excelCellToString()`.

3. **Skip empty rows**  
   For each data row, **`isRowEmpty($row)`** checks if every cell is empty/blank; if yes, the row is not imported.

4. **Normalize to 17 columns**  
   For each row, **`normalizeRowToCanonical($row, $canonical)`** builds a new array keyed by the 17 canonical names:
   - For each canonical column name it looks for a matching key in the file row (case-insensitive, after `normalizeColumnKey`, and using **header aliases** e.g. “Descriptions” → “Description”).
   - If no match, it falls back to the same **position** (index) in the row.
   - Special rule: if “Description” is empty and “Subcategory” has text, “Description” is set to that text (for CICTMO-style files).

5. **UTF-8 clean**  
   **`ensureUtf8Recursive()`** runs over the normalized row so all strings are safe for JSON (non-UTF-8 stripped).

6. **Save**  
   - Create one **ImportBatch** (filename + 17 headers).  
   - For each normalized row, create one **ImportRecord** under that batch with `row_data` = that row. The model’s `setRowDataAttribute` runs and sanitizes again before JSON encode.

7. **Redirect**  
   Redirect to `records.index` with a success message (e.g. “X records imported successfully.”).

So in one sentence: **Upload → parse (CSV or Excel) → skip empty rows → map each row to 17 column names → sanitize UTF-8 → save one batch + N records.**

---

## 6. Records list (index) and search/filter

**`RecordController@index`**:

- Loads **ImportRecord** with `importBatch`, ordered by latest, **paginated (15 per page)**.
- **Search (any column):**  
  If `search` is present, it runs a raw SQL query using SQLite’s `json_each(import_records.row_data)` and `LIKE` on the values, then restricts the query to those record IDs.
- **Filter by column + value:**  
  If both `column` and `value` are present, it uses `json_extract(row_data, '$.Column Name')` and `LIKE` for that column.
- Passes **`$records`** (paginator) and **`$headers`** (= `TABLE_COLUMNS`) to **`records.index`**.

The view **`records/index.blade.php`**:

- Shows a header (“Records”), search/filter form, then either an empty state or:
  - A **top horizontal scroll bar** (a thin div whose scroll is synced with the table body via JavaScript) so you can scroll the table left/right from the top.
  - A **table**: one row per record, one cell per column in `$headers`, plus an **Actions** column (View, Edit, Image/Attach, Delete).
  - Cell content is truncated for display with `Str::words($val, 8)`; the View page shows full content (see below).
- Image preview uses a modal; “Attach” uses a hidden file input that submits a form to `records.attach-image`.

---

## 7. View one record (show)

**`RecordController@show`** receives the **ImportRecord** (from route model binding), loads `importBatch`, and passes **`$record`** and **`$columns`** (= `TABLE_COLUMNS`) to **`records/show.blade.php`**.

The view loops over `$columns` and for each column calls **`$record->getColumn($col)`**. It displays the **full value** (no word limit), so all imported data is visible. Empty values show as “—”. Styling uses `white-space: pre-wrap` so line breaks in the data are preserved. If the record has an `image_path`, the attached image is shown and can be opened in the same modal used on the index.

---

## 8. Edit and update

**`RecordController@edit`** passes **`$record`** and **`$columns`** to **`records/edit.blade.php`**, which renders a form with one input per column; values come from **`$record->getColumn($col)`**.

**`RecordController@update`**:

- Reads current **`$record->row_data`**.
- For each name in **TABLE_COLUMNS**, gets the value from the request.
- For each such value, it finds the matching key in `row_data` using **case-insensitive** comparison and **`ImportRecord::normalizeColumnKey()`**, then updates that key’s value (or adds the key if not found).
- Saves **`row_data`** on the record. The model’s mutator again sanitizes for UTF-8 before JSON encode.
- Redirects to the records list with “Record updated.”

So the form always sends the 17 column names; the controller merges them into the existing `row_data` by normalized key.

---

## 9. Delete and images

**`RecordController@destroy`**:

- If the record has **`image_path`**, it deletes that file from **`storage/app/public`** (e.g. `record-images/...`).
- Deletes the **ImportRecord** row.

**`RecordController@attachImage`**:

- Validates: `image` required, type image (jpeg, png, jpg, gif, webp), max 5120 KB.
- If the record already had an image, deletes the old file.
- Stores the new file under **`record-images/`** on the `public` disk and updates **`import_records.image_path`**.
- Redirects back with “Image attached.”

**`RecordController@image`**:

- Returns the file from **`Storage::disk('public')->path($record->image_path)`** using **`response()->file()`** so the browser can show it (e.g. in the modal or in the show page). Returns 404 if no path or file missing.

Make sure **`php artisan storage:link`** has been run so `public/storage` points to `storage/app/public` and images are reachable.

---

## 10. Clear all import data (Artisan command)

**`ClearImportDataCommand`** (signature: **`imports:clear`**):

- Without **`--force`**: asks for confirmation.
- Deletes every **ImportRecord** (and its image file from storage if `image_path` is set).
- Deletes every **ImportBatch**.
- Prints how many records and batches were deleted.

Run: **`php artisan imports:clear`** or **`php artisan imports:clear --force`**.

---

## 11. Views and layout (short)

- **`layouts/app.blade.php`**  
  Common HTML, nav (Records / Import CSV/Excel), success/error flash messages, **`@yield('content')`**, and **`@stack('styles')`** for page-specific CSS. The layout also sets a subtle page background and nav styling.

- **`imports/create.blade.php`**  
  Import form (file input, Import / Cancel). On submit, JavaScript shows a full-page loading overlay and disables the Import button until the redirect happens.

- **`records/index.blade.php`**  
  Search/filter form, top scroll strip (synced with table scroll), records table, pagination, image preview modal. Uses **TABLE_COLUMNS** for headers and **`$record->getColumn($h)`** for each cell (truncated to 8 words here).

- **`records/show.blade.php`**  
  Back link, card with record title and actions (Edit, Preview/Attach image, Delete), table of all columns with **full** values (no truncation), optional image section, same image modal.

- **`records/edit.blade.php`**  
  Back link, card with “Edit Record #…”, form table (label + input per column), Save / Cancel. Inputs use **`$record->getColumn($col)`** for current value.

---

## 12. Quick reference: where to change what

| If you want to… | Look here |
|-----------------|-----------|
| Change the 17 column names or order | `RecordController::TABLE_COLUMNS` |
| Change import rules (e.g. skip rows, max size) | `ImportController@store` and helpers (`isRowEmpty`, `normalizeRowToCanonical`, validation) |
| Add another file format | `ImportController`: new parser method + branch in `store` |
| Map another CSV header to a column (e.g. “Item” → “Description”) | `ImportController::getHeaderAliases()` and/or `normalizeRowToCanonical` |
| Change how column keys are matched (e.g. trim dots) | `ImportRecord::normalizeColumnKey()` and `getColumn()` |
| Change search/filter (e.g. exact match) | `RecordController@index` (query and raw SQL / json_extract) |
| Change records per page | `RecordController@index` → `paginate(15)` |
| Change image size limit or types | `RecordController@attachImage` validation |
| Change how the table or import page looks | `resources/views/records/index.blade.php`, `imports/create.blade.php`, and their `@push('styles')` |
| Change DB structure | New migration; then adjust models and controllers that use the new columns |

---

## 13. Other docs in this project

- **`docs/DATA_IMPORT_MANUAL.md`** – Step-by-step for running the app, importing, using the records table, and clearing data (user/developer manual).
- **`docs/SYSTEM_EXPLAINED.md`** – Short explanations and Q&A for describing the system to others (e.g. “What is it?”, “How do I run it?”, “Where is the code?”).

Use **this file (CODE_DOCUMENTATION.md)** when you need to understand or modify the code itself.
