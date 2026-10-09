<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('keeps the original name of an uploaded document and links it on the product page', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::factory()->create();

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['documents' => [UploadedFile::fake()->create('SKF 6204 adatlap.pdf', 120, 'application/pdf')]])
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();
    $path = $product->documents[0];
    Storage::disk('public')->assertExists($path);
    expect($product->document_names)->toBe([$path => 'SKF 6204 adatlap.pdf']);

    get(route('products.show', $product))->assertOk()
        ->assertSee('SKF 6204 adatlap.pdf')
        ->assertSee(Storage::disk('public')->url($path), false);
});
