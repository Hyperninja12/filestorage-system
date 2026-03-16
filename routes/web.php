<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\RecordController;
use Illuminate\Support\Facades\Route;

// Redirect sa root ngadto sa records index
Route::get('/', function () {
    return redirect()->route('records.index');
});

Route::get('/import', [ImportController::class, 'create'])->name('import.create');
Route::post('/import', [ImportController::class, 'store'])->name('import.store');

Route::get('/records', [RecordController::class, 'index'])->name('records.index');
Route::get('/records/{record}', [RecordController::class, 'show'])->name('records.show');
Route::get('/records/{record}/edit', [RecordController::class, 'edit'])->name('records.edit');
Route::put('/records/{record}', [RecordController::class, 'update'])->name('records.update');
Route::delete('/records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');
Route::post('/records/{record}/image', [RecordController::class, 'attachImage'])->name('records.attach-image');
Route::get('/records/{record}/image/{index?}', [RecordController::class, 'image'])->name('records.image')->where('index', '[0-1]');
Route::delete('/records/{record}/image/{index}', [RecordController::class, 'removeImage'])->name('records.remove-image')->where('index', '[0-1]');
Route::get('/records/{record}/print', [RecordController::class, 'print'])->name('records.print');
