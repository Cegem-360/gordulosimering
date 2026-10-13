<?php

declare(strict_types=1);

use App\Livewire\LiveSearch;
use App\Livewire\ProductCard;
use App\Livewire\Products\Index;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

/**
 * @param  array<string, mixed>  $attributes
 */
function searchProduct(array $attributes, ?string $brand = null): Product
{
    $product = Product::factory()->create(['stock_quantity' => 5, ...$attributes]);
    $product->forceFill(['brand' => $brand])->saveQuietly();

    return $product;
}

it('lists SKF first among in-stock products matching the code', function (): void {
    searchProduct(['product_code' => '6204ZZ_K', 'name' => 'KOYO 6204 teszt'], 'KOYO');
    searchProduct(['product_code' => '6204-2RSH_S', 'name' => 'SKF 6204 teszt'], 'SKF');
    searchProduct(['product_code' => '6204-2RS_F', 'name' => 'FAG 6204 teszt', 'stock_quantity' => 0], 'FAG');

    $results = Livewire::withQueryParams(['search' => '6204'])
        ->test(Index::class)
        ->set('selectedFilters.stock', ['in_stock'])
        ->instance()->products;
    expect(collect($results->items())->pluck('name')->all())->toBe(['SKF 6204 teszt', 'KOYO 6204 teszt']);

    expect(Livewire::test(LiveSearch::class)->set('query', '6204')->get('results')->pluck('name')->all())
        ->toBe(['SKF 6204 teszt', 'KOYO 6204 teszt', 'FAG 6204 teszt']);
});

it('keeps codes that start with the search term ahead of SKF codes that only contain it', function (): void {
    searchProduct(['product_code' => '16204_S', 'name' => 'SKF 16204 teszt'], 'SKF');
    searchProduct(['product_code' => '6204ZZ_K', 'name' => 'KOYO 6204 teszt'], 'KOYO');

    expect(Livewire::test(LiveSearch::class)->set('query', '6204')->get('results')->pluck('name')->all())
        ->toBe(['KOYO 6204 teszt', 'SKF 16204 teszt']);
});

it('shows the size beside the code for bearings only', function (): void {
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $deepGrooveBall = Category::query()->create(['name' => 'EGYSOROS MÉLYHORNYÚ GOLYÓSCSAPÁGY', 'slug' => 'egysoros', 'category_id' => $bearings->id]);
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);

    $bearing = searchProduct(['product_code' => '6204-2RSH_S', 'size' => '20X47X14']);
    $bearing->categories()->attach($deepGrooveBall);
    $seal = searchProduct(['product_code' => 'SIM 20X35X7', 'size' => '20X35X7']);
    $seal->categories()->attach($seals);

    Livewire::test(ProductCard::class, ['product' => $bearing])->assertSee('Méret: 20X47X14');
    Livewire::test(ProductCard::class, ['product' => $seal])->assertDontSee('Méret:');

    Livewire::test(LiveSearch::class)->set('query', '20X')
        ->assertSee('· 20X47X14')
        ->assertDontSee('· 20X35X7');
});
