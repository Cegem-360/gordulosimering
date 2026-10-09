<?php

declare(strict_types=1);

use App\Livewire\LiveSearch;
use App\Livewire\Products\Categories\Index as CategoriesIndex;
use App\Livewire\Products\Categories\Show;
use App\Livewire\Products\Index;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

/**
 * An orderable product named first alphabetically and an in-stock one after it.
 *
 * @return array{0: Product, 1: Product}
 */
function orderableAndInStockBearings(): array
{
    return [
        Product::factory()->create(['name' => 'Csapágy A rendelhető', 'stock_quantity' => 0]),
        Product::factory()->create(['name' => 'Csapágy B készleten', 'stock_quantity' => 5]),
    ];
}

it('lists the in-stock products before the orderable ones', function (string $component): void {
    orderableAndInStockBearings();

    Livewire::test($component)
        ->assertSeeInOrder(['Csapágy B készleten', 'Csapágy A rendelhető']);
})->with([
    'products page' => [Index::class],
    'all categories page' => [CategoriesIndex::class],
]);

it('lists the in-stock products first on a category page', function (): void {
    $category = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $category->products()->attach(orderableAndInStockBearings());

    Livewire::test(Show::class, ['category' => $category])
        ->assertSeeInOrder(['Csapágy B készleten', 'Csapágy A rendelhető']);
});

it('lists the in-stock products first among the search results', function (): void {
    orderableAndInStockBearings();

    $products = Livewire::test(Index::class)
        ->set('search', 'Csapágy')
        ->instance()
        ->products;

    expect($products->pluck('name')->all())->toBe(['Csapágy B készleten', 'Csapágy A rendelhető']);

    $results = Livewire::test(LiveSearch::class)
        ->set('query', 'Csapágy')
        ->instance()
        ->results;

    expect($results->pluck('name')->all())->toBe(['Csapágy B készleten', 'Csapágy A rendelhető']);
});
