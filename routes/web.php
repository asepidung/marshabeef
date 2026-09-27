<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LabelController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('products', ProductController::class);
Route::patch('products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
Route::resource('labels', LabelController::class);
Route::get('/labels/{label}/print', [LabelController::class, 'print'])->name('labels.print');
Route::resource('types', \App\Http\Controllers\TypeController::class)->except(['show', 'create', 'edit']);
Route::patch('types/{type}/toggle', [\App\Http\Controllers\TypeController::class, 'toggleActive'])->name('types.toggle');
