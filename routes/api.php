<?php
use App\Http\Controllers\Api\ProductSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('products/search', ProductSearchController::class)->name('products.search');
});
