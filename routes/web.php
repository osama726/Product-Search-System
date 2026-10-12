<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('api-demo', [
        'categories' => Category::query()->orderBy('name')->get(),
    ]);
});
