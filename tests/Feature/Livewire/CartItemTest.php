<?php

declare(strict_types=1);

use App\Livewire\CartItem;
use App\Models\Product;
use Illuminate\Support\Number;
use Livewire\Livewire;

it('renders successfully', function (): void {
    $product = Product::factory()->create();

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 1])
        ->assertStatus(200);
});

it('prices a sale line item at the sale price', function (): void {
    $product = Product::factory()->onSale(30)->create(['net_selling_price' => 1000]);

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 2])
        ->assertSee(Number::currency(700, 'HUF', 'hu', 0))
        ->assertSee(Number::currency(1400, 'HUF', 'hu', 0))
        ->assertSeeHtml('line-through');
});

it('shows the net and the gross unit price and line total', function (): void {
    $product = Product::factory()->create(['net_selling_price' => 14692.70, 'gross_selling_price' => 18659.73]);

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 2])
        ->assertSeeInOrder([
            'Nettó egységár', Number::currency(14693, 'HUF', 'hu', 0), '+ÁFA', 'Bruttó: ' . Number::currency(18660, 'HUF', 'hu', 0),
            'Nettó összesen', Number::currency(29385, 'HUF', 'hu', 0), '+ÁFA', 'Bruttó összesen: ' . Number::currency(37319, 'HUF', 'hu', 0),
        ]);
});

it('works out the gross price from the discounted net price', function (): void {
    $product = Product::factory()->onSale(10)->create(['net_selling_price' => 14692.70, 'gross_selling_price' => 18659.73]);

    expect($product->unit_price)->toBe(13223.0)
        ->and($product->gross_unit_price)->toBe(13223.0 * 1.27);

    Livewire::test(CartItem::class, ['productId' => $product->id, 'quantity' => 1])
        ->assertSee('Bruttó: ' . Number::currency(16793, 'HUF', 'hu', 0));
});
