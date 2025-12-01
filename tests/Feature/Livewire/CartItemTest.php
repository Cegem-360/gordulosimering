<?php

use App\Livewire\CartItem;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CartItem::class)
        ->assertStatus(200);
});
