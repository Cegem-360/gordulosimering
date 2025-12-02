<?php

declare(strict_types=1);

use App\Livewire\Cart;
use App\Livewire\CheckOut;
use App\Livewire\OrderDetail;
use App\Livewire\OrderHistory;
use App\Livewire\Pages\CompanyData;
use App\Livewire\Pages\Contact;
use App\Livewire\Pages\DeliveryFramework;
use App\Livewire\Pages\Documents;
use App\Livewire\Pages\PrivacyPolicy;
use App\Livewire\Pages\Services;
use App\Livewire\Pages\Team;
use App\Livewire\Pages\TermsAndConditions;
use App\Livewire\Products\Categories\Index as CategoriesIndex;
use App\Livewire\Products\Categories\Show as CategoriesShow;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Products\Show as ProductsShow;
use App\Livewire\Profile;
use App\Livewire\QualityPolicy;
use App\Livewire\ThankYou;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:global'])->get('/', function () {
    return view('index');
})->name('index');

Route::middleware(['throttle:global'])->prefix('products')->as('products.')->group(function () {
    Route::get('/', ProductsIndex::class)->name('index');
    Route::get('/{product:slug}', ProductsShow::class)->name('show');

    // Testing route for product page layout
    if (app()->environment('local')) {
        Route::get('/test/51050', ProductsShow::class)->name('test');
    }
});

Route::middleware(['throttle:global'])->prefix('termekkategoriak')->as('categories.')->group(function () {
    Route::get('/', CategoriesIndex::class)->name('index');
    Route::get('/{category:slug}', CategoriesShow::class)->name('show');
});

Route::middleware(['throttle:global', 'EnsureCartExists'])->group(function () {
    Route::middleware(['EnsureCartNotEmpty'])->get('/checkout', CheckOut::class)->name('checkout');
    Route::get('/kosar', Cart::class)->name('cart');
    Route::get('/koszonjuk', ThankYou::class)->name('thank-you');
    Route::get('/szolgaltatasaink', Services::class)->name('services');
    Route::get('/munkatarsaink', Team::class)->name('team');
    Route::get('/kapcsolat', Contact::class)->name('contact');
    Route::get('/cegadatok', CompanyData::class)->name('company-data');
    Route::get('/dokumentumok', Documents::class)->name('documents');
    Route::get('/altalanos-szerzodesi-feltetelek', TermsAndConditions::class)->name('terms-and-conditions');
    Route::get('/szallitasi-keretszerzodes', DeliveryFramework::class)->name('delivery-framework');
    Route::get('/minosegpolitika', QualityPolicy::class)->name('quality-policy');
    Route::get('/adatkezelesi-tajekoztato', PrivacyPolicy::class)->name('privacy-policy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profilom', Profile::class)->name('profile');
    Route::get('/rendeleseim', OrderHistory::class)->name('orders.history');
    Route::get('/rendeleseim/{order}', OrderDetail::class)->name('orders.show');
});

require __DIR__ . '/auth.php';
