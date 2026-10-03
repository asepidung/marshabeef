<?php

use App\Http\Controllers\LabelController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [PinController::class, 'show'])->name('login');
Route::post('/login', [PinController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');

Route::middleware('pin')->group(function () {
    Route::post('/logout', [PinController::class, 'logout'])->name('logout');

    Route::resource('products', ProductController::class);
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
    Route::resource('labels', LabelController::class);
    Route::get('/labels/{label}/print', [LabelController::class, 'print'])->name('labels.print');
    Route::resource('types', TypeController::class)->except(['show', 'create', 'edit']);
    Route::patch('types/{type}/toggle', [TypeController::class, 'toggleActive'])->name('types.toggle');
});
