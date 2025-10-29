<?php

use App\Livewire\Product;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Product::class)
        ->assertStatus(200);
});
