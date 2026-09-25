<?php

declare(strict_types=1);

use App\Livewire\Products\Show;
use App\Models\Product;
use Illuminate\Support\Number;
use Livewire\Livewire;

it('renders successfully', function (): void {
    $product = Product::factory()->create();

    Livewire::test(Show::class, ['product' => $product])
        ->assertStatus(200);
});

it('leaves the internal ERP fields out of the product information', function (): void {
    $product = Product::factory()->create([
        'catalog_number' => 'CAT-1234',
        'group_code' => '42',
        'is_service' => false,
        'discount_group' => 'B',
        'quantity_unit' => 'db',
        'secondary_unit' => 'raklap',
        'minimum_stock' => 7,
    ]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSee(['Alapadatok', 'Katalógus szám', 'Árazás', 'Készlet és rendelés', 'Mennyiségi egység'])
        ->assertDontSee(['Csoport kód', 'Szolgáltatás', 'Kedvezmény csoport', 'Másodlagos egység', 'Minimum készlet', 'raklap']);
});

it('shows the sale price on the product page', function (): void {
    $product = Product::factory()->onSale(52)->create(['net_selling_price' => 999]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSee(Number::currency(480, 'HUF', 'hu', 0))
        ->assertSee('-52%')
        ->assertSeeHtml('line-through');
});

it('does not show the full gross price next to the sale price on the product page', function (): void {
    $product = Product::factory()->onSale(52)->create(['net_selling_price' => 999, 'gross_selling_price' => 1269]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertDontSee(Number::currency(1269, 'HUF', 'hu', 0));
});

it('still shows the gross price of a regular product on the product page', function (): void {
    $product = Product::factory()->create(['net_selling_price' => 999, 'gross_selling_price' => 1269]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSee(Number::currency(1269, 'HUF', 'hu', 0));
});
