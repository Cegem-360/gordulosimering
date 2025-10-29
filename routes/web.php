<?php

declare(strict_types=1);

use App\Livewire\Products;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:global'])->get('/', function () {
    return view('index');
});

Route::middleware(['throttle:global'])->prefix('products')->group(function () {

    Route::get('/', Products\Index::class)->name('products.index');
    Route::get('/{product}', Products\Show::class)->name('products.show');

})->rateLimited();
