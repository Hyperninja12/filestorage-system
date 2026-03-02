# Data Import System – How to Explain It

Use this document when someone asks what this project is, how it works, or how to use it. You can answer in your own words using the points below.

---

## 1. What is this system? (Elevator pitch)

**In one sentence:**  
*“It’s a web app where you upload a CSV or Excel file, and it imports the rows into a database so you can view, search, edit, and manage them in a table—including attaching one image per row.”*

**A bit more detail:**  
- You upload **one file** (CSV or Excel).  
- The **first row** of the file is treated as column headers.  
- Every **following row** becomes one **record** in the app.  
- All records are shown in a **table** with fixed columns (like a PPE/property report).  
- You can **search**, **filter**, **view**, **edit**, **delete** records, and **attach one image** per record.

So: **upload → import → manage in a table.**

---

## 2. Who is it for?

- People who have data in **CSV or Excel** and want to:
  - Put it in one place (database).
  - View and search it in a simple table.
  - Edit or delete rows.
  - Attach a photo or image to each row (e.g. asset photo).

- It works **offline** (SQLite database, no internet required for the data).

---

## 3. How do you run it?

1. Open a terminal in the project folder (e.g. `c:\laravel-projects\jesproject`).
2. Run: **`php artisan serve`**
3. In the browser open: **http://127.0.0.1:8000**

The home page is the **Records** list. From there you can go to **Import CSV/Excel** to upload a file.

*If the server won’t start on Windows,* check PHP’s `variables_order` in `php.ini` (see `docs/DATA_IMPORT_MANUAL.md`).

---

## 4. What can users do? (Features)

| Feature | What it means |
|--------|----------------|
| **Import** | Upload a .csv, .xlsx, or .xls file. First row = headers, each other row = one record. A loading screen appears while the file is uploading and processing. |
| **Records table** | See all imported rows in a table with 17 columns (Account Code, Fund, Category, Subcategory, Description, etc.) plus an **Actions** column. |
| **Search** | Search across all columns at once, or filter by a specific column and value. |
| **View** | Open a single record to see all fields and the attached image (if any). |
| **Edit** | Change any field of a record and save. |
| **Delete** | Remove one record (and its image from storage). |
| **Attach image** | Upload one image per record (e.g. photo of the item). |
| **Preview image** | Open the attached image in a popup. |
| **Clear all data** | Run `php artisan imports:clear` to delete all imported records, batches, and images. |

---

## 5. What are the 17 columns?

The app always shows data in these columns (in this order):

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

When you import a file, the app **matches** the file’s headers to these names (case doesn’t matter, and things like “Qty.” or “Po No.” are normalized). So even if the CSV uses slightly different names, the data still goes into the right column.

---

## 6. How does import work? (Technical, for “how does it work?” questions)

1. User selects a file and clicks **Import**.  
2. The file is sent to the server (CSV or Excel).  
3. **CSV** is read with League CSV; **Excel** with PhpSpreadsheet.  
4. First row → headers (duplicate names get a suffix like `_2`).  
5. Blank rows are **skipped**.  
6. Each data row is **mapped** to the 17 column names (with aliases, e.g. “Descriptions” → Description, “Qty.” → Qty).  
7. If the file’s “Description” is empty but “Subcategory” has text, that text is copied into Description so it shows in the table.  
8. Data is stored in the **SQLite** database: one row per record, with column values in a JSON field (`row_data`).  
9. User is redirected to the Records table with a success message.

So: **file → parse → normalize to 17 columns → save to DB → redirect to records.**

---

## 7. Where is the data stored?

- **Database:** SQLite, file at `database/database.sqlite`.  
- **Tables:**  
  - `import_batches` – one row per import (filename, etc.).  
  - `import_records` – one row per imported row; the 17 columns are stored as JSON in `row_data`; optional `image_path` for the attached image.  
- **Images:** Stored under `storage/app/public/record-images/`. You need `php artisan storage:link` once so the app can serve them.

---

## 8. Quick answers to common questions

**“What file formats are supported?”**  
CSV (.csv), Excel (.xlsx, .xls). Max size 10 MB.

**“Does the first row have to be headers?”**  
Yes. The first row is always treated as column names.

**“What if my CSV has different column names?”**  
The app matches by name (case-insensitive). “Qty.”, “Po No.”, “Descriptions” are normalized to Qty, PO No., Description. Empty columns get names like `column_3`.

**“Can I have empty rows in the file?”**  
Yes. Blank rows are skipped and not imported.

**“How do I delete everything and start over?”**  
Run: `php artisan imports:clear` (with `--force` to skip confirmation).

**“Where is the code for import / records / clearing?”**  
- Import: `app/Http/Controllers/ImportController.php`  
- Records (list, view, edit, delete, image): `app/Http/Controllers/RecordController.php`  
- Clear command: `app/Console/Commands/ClearImportDataCommand.php`  
- 17 column names: `RecordController::TABLE_COLUMNS`  
- Routes: `routes/web.php`  
- Views: `resources/views/records/` and `resources/views/imports/create.blade.php`

**“What if I get ‘Malformed UTF-8’ or JSON errors?”**  
The app cleans non-UTF-8 characters on import and when saving, so stored data is safe for JSON. If you still see errors, the file might have very unusual encoding.

---

## 9. Summary table (for quick reference)

| Topic | Answer |
|-------|--------|
| **What it is** | Web app: upload CSV/Excel → import into SQLite → view/edit/search records in a table, with one image per record. |
| **Run it** | `php artisan serve` → open http://127.0.0.1:8000 |
| **Import** | Import CSV/Excel → choose file → Import (loading shows while processing). |
| **Columns** | 17 fixed columns (Account Code through Remarks); file headers are matched to these names. |
| **Database** | SQLite at `database/database.sqlite`; tables: `import_batches`, `import_records`. |
| **Clear all** | `php artisan imports:clear` |
| **More detail** | User/developer steps: `docs/DATA_IMPORT_MANUAL.md` |

---

*Use this document to explain the system and answer questions. For step-by-step user and developer instructions, see `DATA_IMPORT_MANUAL.md`.*
