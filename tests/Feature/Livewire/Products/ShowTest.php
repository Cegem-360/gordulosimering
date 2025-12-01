<?php

declare(strict_types=1);

use App\Livewire\Products\Show;
use App\Models\Product;
use Livewire\Livewire;

it('renders successfully', function () {
    $product = Product::factory()->create();

    Livewire::test(Show::class, ['product' => $product])
        ->assertStatus(200);
});
