<?php

declare(strict_types=1);

use App\Livewire\Products\Show;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Livewire\Livewire;

it('renders successfully', function (): void {
    $product = Product::factory()->create();

    Livewire::test(Show::class, ['product' => $product])
        ->assertStatus(200);
});

it('shows only the product code, name, size, weight and quality of the ERP data', function (): void {
    $product = Product::factory()->create([
        'product_code' => '6204-2RSH_S',
        'name' => 'SKF mélyhornyú golyóscsapágy',
        'size' => '20X47X14',
        'weight' => 0.106,
        'quality' => 'C3',
        'catalog_number' => 'CAT-1234',
        'type' => 'GOLYÓS',
        'product_variety' => 'csapágy mélyhornyú',
        'supplier' => 'SKF Hungary',
        'barcode' => '*BC-998877*',
        'ean_code' => '5901234123457',
        'ksh_number' => '84821010',
        'rating' => 'A-minősítés',
        'description' => 'Hosszú belső megjegyzés',
        'short_note' => 'Rövid belső megjegyzés',
        'custom_fields' => ['Polc' => 'B-12'],
        'discount_group' => 'G02',
        'vat_class' => 'AFA27',
    ]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSee(['6204-2RSH_S', 'SKF mélyhornyú golyóscsapágy', '20X47X14', '0,106 kg', 'C3'])
        ->assertDontSee(['CAT-1234', 'GOLYÓS', 'csapágy mélyhornyú', 'SKF Hungary', 'BC-998877', '5901234123457', '84821010',
            'A-minősítés', 'Hosszú belső megjegyzés', 'Rövid belső megjegyzés', 'B-12', 'G02', 'AFA27', 'Termékinformációk']);
});

it('leads the breadcrumb through the product\'s shop category', function (): void {
    $category = Category::query()->create(['name' => 'EGYSOROS MÉLYHORNYÚ GOLYÓSCSAPÁGY', 'slug' => 'egysoros']);
    $product = Product::factory()->create(['product_variety' => 'csapágy mélyhornyú']);
    $product->categories()->attach($category);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSeeHtml('href="' . route('categories.show', $category) . '"')
        ->assertSee('EGYSOROS MÉLYHORNYÚ GOLYÓSCSAPÁGY');
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

it('links the call prompt to the shop phone number', function (): void {
    Livewire::test(Show::class, ['product' => Product::factory()->create()])
        ->assertSeeHtml('href="tel:+3612611566"')
        ->assertSee('Kérdése van? Hívjon minket!');
});

it('offers a quote request next to the call prompt', function (): void {
    $product = Product::factory()->create(['name' => 'TENTE befeszítőcsap']);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSee('Ajánlatkérés')
        ->assertSeeHtml('wire:click="addToQuote"');
});

it('offers only a call and a quote request for an out-of-stock product', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 0]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSee(['Hívjon', 'Ajánlatkérés'])
        ->assertDontSee(['Kosárba', 'Kérdése van?'])
        ->assertDontSeeHtml('wire:click="increment"')
        ->assertSeeHtml('href="tel:+3612611566"')
        ->call('addToCart')
        ->assertNotDispatched('cartUpdated');
});

it('shows the exact stock count with its unit on the product page', function (): void {
    Livewire::test(Show::class, ['product' => Product::factory()->create(['stock_quantity' => 23, 'quantity_unit' => 'db'])])
        ->assertSee('Raktáron:')
        ->assertSee('23 db');

    Livewire::test(Show::class, ['product' => Product::factory()->create(['stock_quantity' => 12.5, 'quantity_unit' => 'm'])])
        ->assertSee('12,5 m');

    Livewire::test(Show::class, ['product' => Product::factory()->create(['stock_quantity' => 0])])
        ->assertDontSee('Raktáron:');
});

it('links the uploaded documents under their original names', function (): void {
    $product = Product::factory()->create([
        'documents' => ['products/documents/01ABC.pdf', 'products/documents/02DEF.pdf'],
        'document_names' => ['products/documents/01ABC.pdf' => 'SKF katalógus.pdf'],
    ]);

    Livewire::test(Show::class, ['product' => $product])
        ->assertSee(['Dokumentumok', 'SKF katalógus.pdf', 'Dokumentum 2'])
        ->assertDontSeeText('02DEF.pdf')
        ->assertSeeHtml('href="' . Storage::disk('public')->url('products/documents/01ABC.pdf') . '"');
});

it('shows no documents card without uploads', function (): void {
    Livewire::test(Show::class, ['product' => Product::factory()->create(['documents' => null])])
        ->assertDontSee('Dokumentumok');
});
