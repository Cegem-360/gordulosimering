<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('names an uploaded document after its file and links it on the product page', function (): void {
    $product = Product::factory()->create();

    $form = Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->set('data.document_rows', ['row' => ['file' => [], 'name' => null]]);
    $form->set('data.document_rows.row.file', [UploadedFile::fake()->create('SKF 6204 adatlap.pdf', 120, 'application/pdf')])
        ->assertSet('data.document_rows.row.name', 'SKF 6204 adatlap')
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();
    $path = $product->documents[0];
    Storage::disk('public')->assertExists($path);
    expect($product->document_names)->toBe([$path => 'SKF 6204 adatlap']);

    get(route('products.show', $product))->assertOk()
        ->assertSee('SKF 6204 adatlap')
        ->assertSee(Storage::disk('public')->url($path), false);
});

it('lets the admin rename and reorder documents', function (): void {
    Storage::disk('public')->put('products/documents/a.pdf', 'a');
    Storage::disk('public')->put('products/documents/b.pdf', 'b');
    $product = Product::factory()->create([
        'documents' => ['products/documents/a.pdf', 'products/documents/b.pdf'],
        'document_names' => ['products/documents/a.pdf' => 'Régi név'],
    ]);

    $form = Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()]);
    $rows = $form->get('data.document_rows');
    expect(collect($rows)->pluck('name')->all())->toBe(['Régi név', null]);

    [$first, $second] = array_keys($rows);
    $form->set('data.document_rows', [
        $second => [...$rows[$second], 'name' => 'Katalógus'],
        $first => [...$rows[$first], 'name' => ' '],
    ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh()->documents)->toBe(['products/documents/b.pdf', 'products/documents/a.pdf'])
        ->and($product->document_names)->toBe(['products/documents/b.pdf' => 'Katalógus'])
        ->and(collect($product->documentLinks())->pluck('name')->all())->toBe(['Katalógus', 'Dokumentum 2']);
});

it('keeps a product without documents empty', function (): void {
    $product = Product::factory()->create(['documents' => null]);

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertSet('data.document_rows', [])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->refresh()->documents)->toBe([])
        ->and($product->document_names)->toBeNull();
});

it('saves documents on a new product too', function (): void {
    Livewire::test(CreateProduct::class)
        ->fillForm(['name' => 'Új termék', 'slug' => 'uj-termek'])
        ->set('data.document_rows', ['row' => ['file' => [], 'name' => 'Adatlap']])
        ->set('data.document_rows.row.file', [UploadedFile::fake()->create('lap.pdf', 10, 'application/pdf')])
        ->assertSet('data.document_rows.row.name', 'Adatlap')
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::query()->where('slug', 'uj-termek')->sole();
    expect($product->document_names)->toBe([$product->documents[0] => 'Adatlap']);
});
