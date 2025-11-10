<?php

declare(strict_types=1);

use App\Livewire\CompanyData;
use App\Livewire\Contact;
use App\Livewire\DeliveryFramework;
use App\Livewire\Documents;
use App\Livewire\PrivacyPolicy;
use App\Livewire\Products;
use App\Livewire\QualityPolicy;
use App\Livewire\Services;
use App\Livewire\Team;
use App\Livewire\TermsAndConditions;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:global'])->get('/', function () {
    return view('index');
});

Route::middleware(['throttle:global'])->prefix('products')->as('products.')->group(function () {
    Route::get('/', Products\Index::class)->name('index');
    Route::get('/{product:slug}', Products\Show::class)->name('show');

    // Testing route for product page layout
    if (app()->environment('local')) {
        Route::get('/test/51050', Products\Show::class)->name('test');
    }
});

Route::middleware(['throttle:global'])->prefix('termekkategoriak')->as('categories.')->group(function () {
    Route::get('/', function () {
        return view('pages.categories.index');
    })->name('index');

    Route::get('/', Products\Categories\Index::class)->name('index');
    Route::get('/{category:slug}', Products\Categories\Show::class)->name('show');
});

Route::middleware(['throttle:global'])->get('/szolgaltatasaink', Services::class)->name('services');
Route::middleware(['throttle:global'])->get('/munkatarsaink', Team::class)->name('team');
Route::middleware(['throttle:global'])->get('/kapcsolat', Contact::class)->name('contact');
Route::middleware(['throttle:global'])->get('/cegadatok', CompanyData::class)->name('company-data');
Route::middleware(['throttle:global'])->get('/dokumentumok', Documents::class)->name('documents');
Route::middleware(['throttle:global'])->get('/altalanos-szerzodesi-feltetelek', TermsAndConditions::class)->name('terms-and-conditions');
Route::middleware(['throttle:global'])->get('/szallitasi-keretszerzodes', DeliveryFramework::class)->name('delivery-framework');
Route::middleware(['throttle:global'])->get('/minosegpolitika', QualityPolicy::class)->name('quality-policy');
Route::middleware(['throttle:global'])->get('/adatkezelesi-tajekoztato', PrivacyPolicy::class)->name('privacy-policy');
