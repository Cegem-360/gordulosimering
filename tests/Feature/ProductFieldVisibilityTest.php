<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('shows the quality on the product page until it is switched off', function (): void {
    $product = Product::factory()->create(['quality' => 'C3']);

    expect($product->showsField('quality'))->toBeTrue();
    get(route('products.show', $product))->assertOk()->assertSee(['Minőség:', 'C3']);

    $product->update(['field_visibility' => ['quality' => false]]);
    get(route('products.show', $product))->assertOk()->assertDontSee('Minőség:');
});

it('lets the admin switch the quality off per product and keeps the other fields internal', function (): void {
    actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::factory()->create(['quality' => 'C3', 'supplier' => 'SKF Hungary']);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSchemaStateSet(['field_visibility.quality' => true])
        ->assertFormFieldDoesNotExist('field_visibility.supplier')
        ->fillForm(['field_visibility.quality' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh()->showsField('quality'))->toBeFalse()
        ->and($product->quality)->toBe('C3');
});
