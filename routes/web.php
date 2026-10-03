<?php

use App\Http\Controllers\LabelController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TypeController;
use Illuminate\Support\Facades\Route;

// Dashboard: video + PIN. Setelah PIN benar langsung ke halaman cetak label.
Route::get('/', [PinController::class, 'show'])->name('login');
Route::get('/login', fn () => redirect()->route('login'));
Route::post('/login', [PinController::class, 'login'])->name('login.attempt');
Route::get('/lock', [PinController::class, 'lock'])->name('lock');

Route::middleware('pin')->group(function () {
    Route::get('/keepalive', [PinController::class, 'keepalive'])->name('keepalive');
    Route::post('/logout', [PinController::class, 'logout'])->name('logout');

    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
    Route::resource('labels', LabelController::class);
    Route::get('/labels/{label}/print', [LabelController::class, 'print'])->name('labels.print');
    Route::resource('types', TypeController::class)->except(['show', 'create', 'edit']);
    Route::patch('types/{type}/toggle', [TypeController::class, 'toggleActive'])->name('types.toggle');
});
