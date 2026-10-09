<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
});

it('finds products by product code in the admin global search', function (): void {
    Product::factory()->create(['name' => 'Gördülőcsapágy', 'product_code' => 'SKF-6204-2RS']);
    Product::factory()->create(['name' => 'Szimering', 'product_code' => 'NBR-25x40']);

    $results = ProductResource::getGlobalSearchResults('6204');

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Gördülőcsapágy')
        ->and($results->first()->details)->toBe(['Cikkszám' => 'SKF-6204-2RS']);
});

it('still finds products by name in the admin global search', function (): void {
    Product::factory()->create(['name' => 'Gördülőcsapágy', 'product_code' => 'SKF-6204-2RS']);

    expect(ProductResource::getGlobalSearchResults('csapágy'))->toHaveCount(1);
});

it('shows the dimensions, brand and material computed for the filters on the product page', function (): void {
    $product = Product::factory()->create(['name' => 'SKF egysorú mélyhornyú golyóscsapágy', 'size' => '17,5X40X12,25']);

    $this->get(ProductResource::getUrl('view', ['record' => $product]))
        ->assertSuccessful()
        ->assertSee(['Belső átmérő (d)', '17,5 mm', 'Külső átmérő (D)', '40 mm', 'Szélesség (B)', '12,25 mm', 'Márka']);
});

it('offers the computed dimensions as hidden, sortable columns in the product list', function (): void {
    $product = Product::factory()->create(['size' => '17,5X40X12,25']);

    $table = Livewire::test(ListProducts::class);

    foreach (['inner_diameter', 'outer_diameter', 'width', 'brand', 'material'] as $column) {
        $table->assertTableColumnExists($column)->assertCanNotRenderTableColumn($column);
    }

    $table->assertTableColumnStateSet('inner_diameter', 17.5, $product)
        ->sortTable('inner_diameter')
        ->assertCanSeeTableRecords([$product]);
});
