<?php

declare(strict_types=1);

use App\Livewire\Products\Show;
use App\Models\Product;
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
