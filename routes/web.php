<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShopifyController;

Route::get('/', [UploadController::class, 'index'])->name('upload.index');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard/upload/{upload}', [DashboardController::class, 'show'])->name('dashboard.show');

Route::get('/shopify/collection', [ShopifyController::class, 'collection'])->name('shopify.collection');
