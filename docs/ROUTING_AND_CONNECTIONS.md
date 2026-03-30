# Routing, Controllers & Connections

This document describes how routes, controllers, models, middleware, and views connect in the JES Project.

---

## 1. Overview

- **Entry:** User hits the app → if not unlocked, they see the **unlock screen** (`/unlock`). After correct password, session `system_unlocked` is set and they can access the rest of the app.
- **Protected area:** All app routes (home, import, records) use the **`system.lock`** middleware: no `system_unlocked` in session → redirect to `/unlock`.
- **Home:** `/` redirects to **Records** list (`records.index`).
- **Import:** Upload CSV/Excel → creates an **ImportBatch** and many **ImportRecord** rows.
- **Records:** List, create, view, edit, delete records; attach/remove images; print view.

---

## 2. Routes

| Method | URI | Name | Controller | Middleware | Notes |
|--------|-----|------|------------|------------|--------|
| GET | `/unlock` | `unlock` | Closure | — | Shows unlock form if not unlocked; else redirect `/` |
| POST | `/unlock` | `unlock.submit` | UnlockController@unlock | — | Validates password, sets session, redirect intended |
| GET | `/unlock-check` | `unlock.debug` | UnlockController@debugPasswordLength | — | Debug only (APP_DEBUG=true): password length / .env info |
| POST | `/lock` | `lock` | UnlockController@lock | system.lock | Clears session, redirect to `/unlock` |
| GET | `/` | — | Closure | system.lock | Redirect to `records.index` |
| GET | `/import` | `import.create` | ImportController@create | system.lock | Import form |
| POST | `/import` | `import.store` | ImportController@store | system.lock | Process file, create batch + records |
| GET | `/records` | `records.index` | RecordController@index | system.lock | List records (search, filter, PAR/ICS) |
| GET | `/records/create` | `records.create` | RecordController@create | system.lock | Form to add one record manually |
| POST | `/records` | `records.store` | RecordController@store | system.lock | Save new record (manual) |
| GET | `/records/{record}` | `records.show` | RecordController@show | system.lock | View one record |
| GET | `/records/{record}/edit` | `records.edit` | RecordController@edit | system.lock | Edit form |
| PUT | `/records/{record}` | `records.update` | RecordController@update | system.lock | Save edits |
| DELETE | `/records/{record}` | `records.destroy` | RecordController@destroy | system.lock | Delete record + images |
| POST | `/records/{record}/image` | `records.attach-image` | RecordController@attachImage | system.lock | Upload image for record |
| GET | `/records/{record}/image/{index?}` | `records.image` | RecordController@image | system.lock | Serve image file (index 0 or 1) |
| DELETE | `/records/{record}/image/{index}` | `records.remove-image` | RecordController@removeImage | system.lock | Remove image at index |
| GET | `/records/{record}/print` | `records.print` | RecordController@print | system.lock | Print-friendly view |

---

## 3. Controllers

### UnlockController  
**File:** `app/Http/Controllers/UnlockController.php`

| Method | Purpose | Uses | Returns |
|--------|---------|------|--------|
| `unlock(Request)` | Validate password, set session | `.env` / config `SYSTEM_PASSWORD` | Redirect intended URL or back with errors |
| `debugPasswordLength()` | Debug password config | `.env`, `Env::get`, config | Plain text (only if APP_DEBUG) |
| `lock(Request)` | Log out / lock | Session | Redirect `/unlock` |

- Password is read from `.env` (`SYSTEM_PASSWORD`) or config; normalized (trim, no control chars).
- Session key: `system_unlocked` (set on success, cleared by `lock`).

---

### ImportController  
**File:** `app/Http/Controllers/ImportController.php`

| Method | Purpose | Uses | Returns |
|--------|---------|------|--------|
| `create()` | Show upload form | — | View `imports.create` |
| `store(Request)` | Parse file, normalize rows, save | ImportBatch, ImportRecord, RecordController::TABLE_COLUMNS, League CSV, PhpSpreadsheet | Redirect `records.index` with success count |

- **create:** Renders `resources/views/imports/create.blade.php`.
- **store:** Validates `file` (csv, xlsx, xls, max 10MB). First row = headers, rest = data. Rows normalized to `RecordController::TABLE_COLUMNS`; empty rows skipped. Creates one **ImportBatch** and many **ImportRecord** via `$batch->records()->create([...])`. UTF-8 cleaned before save.

---

### RecordController  
**File:** `app/Http/Controllers/RecordController.php`

Constants: `MANUAL_BATCH_FILENAME`, `PAR_ICS_THRESHOLD` (50000), `TABLE_COLUMNS` (17 column names).

| Method | Purpose | Uses | Returns |
|--------|---------|------|--------|
| `index(Request)` | List records, search, filter, PAR/ICS | ImportRecord, DB (json search), TABLE_COLUMNS | View `records.index` with `records`, `headers`, `type` |
| `show(ImportRecord)` | Single record view | ImportRecord, TABLE_COLUMNS | View `records.show` |
| `create()` | Manual add form | TABLE_COLUMNS | View `records.create` |
| `store(Request)` | Save manual record | ImportBatch (firstOrCreate Manual), ImportRecord | Redirect `records.show` |
| `edit(ImportRecord)` | Edit form | ImportRecord, TABLE_COLUMNS | View `records.edit` |
| `update(Request, ImportRecord)` | Save edits to row_data | ImportRecord::normalizeColumnKey | Redirect `records.index` |
| `destroy(ImportRecord)` | Delete record and images | Storage (public), ImportRecord | Redirect `records.index` |
| `attachImage(Request, ImportRecord)` | Upload image | Storage (record-images), ImportRecord::MAX_IMAGES | Back with success/error |
| `removeImage(ImportRecord, index)` | Remove image at index | Storage, ImportRecord | Back with success/error |
| `image(ImportRecord, index)` | Serve image file | Storage::disk('public') | response()->file() or 404 |
| `print(ImportRecord)` | Print layout | ImportRecord, TABLE_COLUMNS | View `records.print` with `data`, `imageUrls` |

- **Views:** `records.index`, `records.show`, `records.create`, `records.edit`, `records.print`.
- **PAR/ICS:** Filter by Unit Value (≥50,000 = PAR, &lt;50,000 = ICS) via raw SQL on `row_data` JSON.

---

## 4. Models

### ImportBatch  
**File:** `app/Models/ImportBatch.php`

- **Table:** `import_batches`  
- **Fillable:** `filename`, `headers` (array cast)  
- **Relationship:** `records()` → HasMany **ImportRecord**

Used by: ImportController (create batch), RecordController (manual batch via firstOrCreate).

---

### ImportRecord  
**File:** `app/Models/ImportRecord.php`

- **Table:** `import_records`  
- **Fillable:** `import_batch_id`, `row_data`, `image_paths`  
- **Casts:** `row_data` => array, `image_paths` => array  
- **Constant:** `MAX_IMAGES = 2`  
- **Relationship:** `importBatch()` → BelongsTo **ImportBatch**

**Methods:**

- `getImagePaths()` → array of stored image paths (up to 2).
- `getColumn(string $key)` → value from `row_data` (exact/case-insensitive/key normalized).
- `setColumn(string $key, mixed $value)` → set value in `row_data`.
- `normalizeColumnKey(string)` (static) → trim, BOM, trailing dot for matching.

Used by: ImportController (create records), RecordController (all record actions).

---

## 5. Middleware

### system.lock  
**File:** `app/Http/Middleware/SystemLockMiddleware.php`  
**Registered:** `bootstrap/app.php` → `'system.lock' => SystemLockMiddleware::class`

- **Behavior:** If session does not have `system_unlocked`, redirect to `/unlock`.
- **Used on:** `/`, `/import`, all `/records/*` routes, and explicitly on POST `/lock`.

---

## 6. How It All Connects

```
[Browser]
    │
    ├─ GET /unlock ──────────────► Closure or redirect /  (no middleware)
    ├─ POST /unlock ─────────────► UnlockController@unlock → session + redirect
    ├─ GET /unlock-check ────────► UnlockController@debugPasswordLength (debug)
    ├─ POST /lock ───────────────► UnlockController@lock (middleware: system.lock)
    │
    └─ [system.lock] ────────────► SystemLockMiddleware
            │                         │ no session → redirect /unlock
            ▼                         │ has session → continue
    ┌───────────────────────────────────────────────────────────────┐
    │ GET /           → redirect records.index                        │
    │ GET/POST /import → ImportController → ImportBatch, ImportRecord │
    │ /records/*      → RecordController  → ImportRecord, ImportBatch │
    └───────────────────────────────────────────────────────────────┘
```

**Controller → View:**

- Unlock: `unlock` (view only for GET; POST uses UnlockController).
- Import: `imports.create` (ImportController@create).
- Records: `records.index`, `records.show`, `records.create`, `records.edit`, `records.print` (RecordController).

**Controller → Model:**

- UnlockController: no models; reads .env/config.
- ImportController: **ImportBatch** (create), **ImportRecord** (create via batch).
- RecordController: **ImportBatch** (firstOrCreate for manual), **ImportRecord** (CRUD, images, print).

**Route names** (for `route()`, `redirect()->route()`):

- `unlock`, `unlock.submit`, `unlock.debug`, `lock`
- `import.create`, `import.store`
- `records.index`, `records.create`, `records.store`, `records.show`, `records.edit`, `records.update`, `records.destroy`, `records.attach-image`, `records.image`, `records.remove-image`, `records.print`

---

## 7. View Files (Blade)

| View | Used by |
|------|--------|
| `layouts.app` | Layout for all main pages (records, import) |
| `unlock` | GET `/unlock` (lock screen) |
| `imports.create` | ImportController@create |
| `records.index` | RecordController@index |
| `records.show` | RecordController@show |
| `records.create` | RecordController@create |
| `records.edit` | RecordController@edit |
| `records.print` | RecordController@print |

---

*This document reflects the state of `routes/web.php`, the controllers, models, and middleware in the project.*
