y# Data Import System – User & Developer Manual

This manual describes the Laravel data import system in this project: what it does, how to use it, and how it was built.

---

## 1. What This System Does

- **Upload** CSV or Excel (`.csv`, `.xlsx`, `.xls`) and import rows into an **offline SQLite** database.
- **View** all imported rows in a table with fixed columns (PPE report format).
- **Edit** and **delete** any record.
- **Attach one image** per record and **preview** it.
- **Search** (any column) and **filter** by a specific column.

---

## 2. How to Run the App

1. Open a terminal in the project folder:  
   `c:\laravel-projects\jesproject`

2. Start the server:
   ```bash
   php artisan serve
   ```

3. In the browser go to:  
   **http://127.0.0.1:8000**

If you get **“Failed to listen on 127.0.0.1:8000 (reason: ?)”** on Windows, fix PHP’s `variables_order`:

- Open the `php.ini` file (see path with `php --ini`).
- Find: `variables_order = "EGPCS"`
- Change to: `variables_order = "GPCS"`
- Save and try `php artisan serve` again.

---

## 3. Database

- **Database:** SQLite (offline).  
  File: `database/database.sqlite`
- **Tables:**
  - `import_batches` – one row per file import (filename, headers).
  - `import_records` – one row per imported data row; columns are stored as JSON in `row_data`; optional `image_path` for the attached image.

To create or update tables after pulling the project:

```bash
php artisan migrate
```

---

## 4. Fixed Table Columns

The records table always shows these **17 columns** in this order:

1. Account Code  
2. Fund  
3. Category  
4. Subcategory  
5. Description  
6. Date of Purchase  
7. Property No.  
8. PO No.  
9. Unit  
10. Qty  
11. Unit Value  
12. On Hand Count  
13. On Hand Value  
14. Person Responsible  
15. Office  
16. Additional Information  
17. Remarks  

They are defined in `RecordController::TABLE_COLUMNS` and used on the list, view, and edit screens. Empty cells show **—**. On import, file headers are matched to these names (case-insensitive) so data appears in the correct columns.

---

## 5. How to Import Data

1. Go to **http://127.0.0.1:8000**
2. Click **“Import CSV/Excel”**
3. Choose a `.csv`, `.xlsx`, or `.xls` file (max 10 MB).
4. Click **Import**

Rules:

- **First row** of the file = column headers.
- **Blank rows** (e.g. empty line after the header) are skipped and not imported.
- Headers like **"Qty."** or **"Po No."** (with a trailing period) are matched to **Qty** and **PO No.** automatically.
- **Duplicate header names** (e.g. two “Remarks”) are made unique (e.g. “Remarks_2”) so import does not fail.
- **Non–UTF-8 characters** are cleaned so the data can be stored as JSON without errors.

A sample file with the 17 columns is in the project root: **`sample_ppe_report.csv`**. You can use it to test import.

---

## 6. Using the Records Table

- **Records** – Lists all imported rows in a table with the 17 columns and an **Actions** column.
- **Search** – Type in “Search (any column)” and click Search to filter by any field.
- **Filter** – Choose “Filter by column” and “Filter value”, then Search.
- **View** – Opens one record with all 16 fields and attached image (if any).
- **Edit** – Form with one input per column; empty = placeholder “—”.
- **Delete** – Deletes the record and its attached image file.
- **Attach image** – One image per record (e.g. photo of the asset). Stored under `storage/app/public/record-images/`.
- **Preview image** – Opens the attached image in a modal.

Make sure the storage link exists so images work:

```bash
php artisan storage:link
```

---

## 7. Deleting All Imported Data

To remove **all** import records, batches, and attached images:

```bash
php artisan imports:clear
```

Confirm when asked. To skip confirmation (e.g. in scripts):

```bash
php artisan imports:clear --force
```

---

## 8. Where Things Are in the Code

| What | Where |
|------|--------|
| Fixed 17 column names | `app/Http/Controllers/RecordController.php` → `TABLE_COLUMNS` |
| Import (upload + parse) | `app/Http/Controllers/ImportController.php` |
| Record list, show, edit, delete, image | `app/Http/Controllers/RecordController.php` |
| Models | `app/Models/ImportBatch.php`, `app/Models/ImportRecord.php` |
| Clear-all command | `app/Console/Commands/ClearImportDataCommand.php` |
| Routes | `routes/web.php` |
| Views | `resources/views/records/` (index, show, edit), `resources/views/imports/create.blade.php`, `resources/views/layouts/app.blade.php` |
| Migrations | `database/migrations/` (import_batches, import_records) |
| Sample CSV | `sample_ppe_report.csv` (project root) |

---

## 9. Technical Notes (for developers)

- **Duplicate CSV headers** – Resolved in `ImportController::makeHeadersUnique()` so the first row never has duplicate keys.
- **UTF-8 / JSON errors** – `ImportController::ensureUtf8Recursive()` and `ImportRecord::setRowDataAttribute()` sanitize strings so `row_data` always encodes to JSON.
- **Column name mismatch** – `ImportRecord::getColumn()` does exact match then case-insensitive match so “Account Code” and “account code” both work.
- **Table styling** – Records table uses custom CSS (striped rows, sticky header, compact cells, action buttons) and horizontal scroll so all 17 columns are visible.

---

## 10. Quick Reference

| Task | Action |
|------|--------|
| Run app | `php artisan serve` → open http://127.0.0.1:8000 |
| Import file | Records → “Import CSV/Excel” → choose file → Import |
| Search/filter | Use the form above the table → Search |
| Edit record | Click “Edit” on a row → change fields → Save |
| Attach image | “Attach” on a row (or on View page) → choose image |
| Delete one record | “Delete” on that row |
| Delete all import data | `php artisan imports:clear` |
| Recreate DB tables | `php artisan migrate:fresh` (wipes entire DB) |

---

*Manual covers the Data Import system as built in this project. For Laravel in general, see [laravel.com/docs](https://laravel.com/docs).*
