<?php

use App\Http\Controllers\ImportController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\UnlockController;
use Illuminate\Support\Facades\Route;

// Unlock system — wala’y middleware, aron makita ang lock screen
Route::get('/unlock', function () {
    if (session()->has('system_unlocked')) {
        return redirect('/');
    }
    return view('unlock');
})->name('unlock');
Route::post('/unlock', [UnlockController::class, 'unlock'])->name('unlock.submit');
Route::get('/unlock-check', [UnlockController::class, 'debugPasswordLength'])->name('unlock.debug');
Route::post('/lock', [UnlockController::class, 'lock'])->name('lock')->middleware('system.lock');

// Tanan nga routes sa system — kinahanglan unlocked
Route::middleware(['system.lock'])->group(function (): void {
    Route::get('/', function () {
        return redirect()->route('records.index');
    });

    Route::get('/import', [ImportController::class, 'create'])->name('import.create');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    Route::get('/module-two', function () {
        return view('module-two.index');
    })->name('module-two.index');

    Route::get('/records', [RecordController::class, 'index'])->name('records.index');
    Route::get('/records/create', [RecordController::class, 'create'])->name('records.create');
    Route::post('/records', [RecordController::class, 'store'])->name('records.store');
    Route::get('/records/{record}', [RecordController::class, 'show'])->name('records.show');
    Route::get('/records/{record}/edit', [RecordController::class, 'edit'])->name('records.edit');
    Route::put('/records/{record}', [RecordController::class, 'update'])->name('records.update');
    Route::delete('/records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');
    Route::post('/records/{record}/image', [RecordController::class, 'attachImage'])->name('records.attach-image');
    Route::get('/records/{record}/image/{index?}', [RecordController::class, 'image'])->name('records.image')->where('index', '[0-1]');
    Route::delete('/records/{record}/image/{index}', [RecordController::class, 'removeImage'])->name('records.remove-image')->where('index', '[0-1]');
    Route::get('/records/{record}/print', [RecordController::class, 'print'])->name('records.print');
});
