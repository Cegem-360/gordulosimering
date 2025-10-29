<?php

declare(strict_types=1);

use App\Livewire\Products;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:global'])->get('/', function () {
    return view('index');
});

Route::middleware(['throttle:global'])->prefix('products')->as('products.')->group(function () {

    Route::get('/', Products\Index::class)->name('index');
    Route::get('/{product:slug}', Products\Show::class)->name('show');

});

Route::middleware(['throttle:global'])->prefix('categories')->as('categories.')->group(function () {

    Route::get('/', Products\Categories\Index::class)->name('index');
    Route::get('/{category:slug}', Products\Categories\Show::class)->name('show');

});
