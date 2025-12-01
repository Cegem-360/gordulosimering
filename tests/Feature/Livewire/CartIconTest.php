<?php

declare(strict_types=1);

use App\Livewire\CartIcon;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CartIcon::class)
        ->assertStatus(200);
});

it('shows zero when cart is empty', function () {
    Livewire::test(CartIcon::class)
        ->assertSet('itemCount', 0)
        ->assertSet('total', 0);
});
