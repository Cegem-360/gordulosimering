<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('shows every field on the product page until it is switched off', function (): void {
    $product = Product::factory()->create(['supplier' => 'SKF Hungary', 'ean_code' => '5901234123457', 'short_note' => 'Raktárról azonnal']);

    expect($product->showsField('supplier'))->toBeTrue();

    get(route('products.show', $product))->assertOk()
        ->assertSee(['SKF Hungary', '5901234123457', 'Raktárról azonnal']);
});

it('hides the switched-off fields on the product page only', function (): void {
    $product = Product::factory()->create([
        'supplier' => 'SKF Hungary',
        'ean_code' => '5901234123457',
        'short_note' => 'Raktárról azonnal',
        'field_visibility' => ['supplier' => false, 'short_note' => false],
    ]);

    get(route('products.show', $product))->assertOk()
        ->assertSee('5901234123457')
        ->assertDontSee(['SKF Hungary', 'Raktárról azonnal', 'Megjegyzés']);
});

it('drops the codes card when nothing in it is visible', function (): void {
    $product = Product::factory()->create([
        'barcode' => '*BETA!1036*', 'ean_code' => null, 'ksh_prefix' => null, 'ksh_number' => null,
        'supplier' => null, 'quality' => null, 'rating' => null,
        'field_visibility' => ['barcode' => false],
    ]);

    get(route('products.show', $product))->assertOk()->assertDontSee('Azonosítók és kódok');
});

it('lets the admin switch a field off and on per product', function (): void {
    actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::factory()->create(['supplier' => 'SKF Hungary']);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSchemaStateSet(['field_visibility.supplier' => true, 'field_visibility.ean_code' => true])
        ->fillForm(['field_visibility.supplier' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh()->showsField('supplier'))->toBeFalse()
        ->and($product->showsField('ean_code'))->toBeTrue()
        ->and($product->supplier)->toBe('SKF Hungary');
});
