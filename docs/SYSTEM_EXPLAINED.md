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

### Running as a server (offline / this PC as server)

To use **this PC as a server** so other devices on your network (or only this PC, offline) can open the app:

1. **Same as above, but bind to all interfaces:**  
   `php artisan serve --host=0.0.0.0 --port=8000`  
   - On this PC: **http://127.0.0.1:8000**  
   - From other devices: **http://YOUR_PC_IP:8000** (e.g. `http://192.168.8.102:8000`). Find your IP with `ipconfig` (Windows) or `ip a` (Linux).

2. **Start the server (recommended):**  
   Double‑click **`start-server.bat`** in the project folder. It uses PM2 so the server runs in the background and the window closes; the app stays running.  
   - Needs Node.js and PM2 once: `npm install -g pm2`  
   - **Start when Windows boots:** Run `pm2 save` once, then put **`pm2-resurrect-on-boot.bat`** in your Startup folder (`Win+R` → `shell:startup`). Or use `pm2-windows-startup` (see `docs/AUTO_START_WINDOWS.md`).

3. **Without PM2:**  
   In a terminal in the project folder, run: `php artisan serve --host=0.0.0.0 --port=8000`. The server runs until you close the terminal.

No internet is required; everything runs on your local network (or only on this machine).

**Can't access the system?**  
- **On this PC:** Open **http://127.0.0.1:8000** (or **http://localhost:8000**). Don’t use the PC’s LAN IP on the same machine if you already use localhost.  
- **From another device:** Open **http://YOUR_PC_IP:8000** (e.g. `http://192.168.8.102:8000`). Find the PC’s IP with `ipconfig` on the server PC.  
- **Connection refused / page won’t load:** (1) Check that jesproject is running: `pm2 list` and `pm2 logs jesproject`. (2) If you use the PC’s IP from another device, **From another device (e.g. 192.168.8.102:8000):** Windows Firewall often blocks port 8000. Right‑click **`allow-port-8000-firewall.bat`** in the project folder → **Run as administrator** (once). Or in an **Admin** PowerShell: `netsh advfirewall firewall add rule name="JES Project (port 8000)" dir=in action=allow protocol=TCP localport=8000`.  
- **Unlock screen:** Use the password from **`.env`** (`SYSTEM_PASSWORD=...`). If you change it, run `php artisan config:clear`.  
- **Unlock works but then it asks for password again:** Use **one URL for the whole session** (e.g. always http://127.0.0.1:8000 on this PC, or always http://PC_IP:8000 from another device). If you mix localhost and IP, the session cookie from one doesn’t apply to the other.

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
