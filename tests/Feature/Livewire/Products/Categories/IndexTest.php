<?php

declare(strict_types=1);

use App\Livewire\Products\Categories\Index;
use App\Livewire\Products\Index as ProductsIndex;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(Index::class)
        ->assertStatus(200);
});

it('offers stock, category and size filters but no quality filter', function (string $component): void {
    Livewire::test($component)
        ->assertSee(['Készlet', 'Kategória', 'Méret'])
        ->assertDontSee('Minőség');

    expect(collect(Livewire::test($component)->instance()->filters)->pluck('key')->all())->toBe(['stock', 'category', 'size']);
})->with([
    'category index' => [Index::class],
    'product list' => [ProductsIndex::class],
]);

it('lists the real top-level categories with the products of their whole subtree', function (): void {
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $ball = Category::query()->create(['name' => 'GOLYÓS CSAPÁGY', 'slug' => 'golyos', 'category_id' => $bearings->id]);
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $brands = Category::query()->create(['name' => Category::BRAND_ROOT_NAME, 'slug' => 'markak']);
    Category::query()->create(['name' => 'ÜRES', 'slug' => 'ures']);
    $ball->products()->attach(Product::factory()->count(2)->create(['product_variety' => 'csapágy SKF']));
    $bearings->products()->attach($direct = Product::factory()->create());
    $seals->products()->attach($seal = Product::factory()->create());
    $brands->products()->attach($seal);

    $categoryFilter = collect(Livewire::test(Index::class)->instance()->filters)->firstWhere('key', 'category');

    expect($categoryFilter['items'])->toBe([
        ['name' => 'CSAPÁGYAK', 'value' => (string) $bearings->id, 'count' => 3],
        ['name' => 'TÖMÍTÉSEK', 'value' => (string) $seals->id, 'count' => 1],
    ]);
});

it('filters by a category including its subcategories and labels the chip with its name', function (): void {
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $ball = Category::query()->create(['name' => 'GOLYÓS CSAPÁGY', 'slug' => 'golyos', 'category_id' => $bearings->id]);
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $ball->products()->attach($bearing = Product::factory()->create(['name' => 'SKF golyóscsapágy']));
    $seals->products()->attach(Product::factory()->create(['name' => 'Simmering NBR']));

    $component = Livewire::test(Index::class)->set('selectedFilters.category', [(string) $bearings->id]);

    expect($component->instance()->products->pluck('id')->all())->toBe([$bearing->id]);
    expect($component->html())->toMatch('/wire:key="chip-category-' . $bearings->id . '"[^>]*>\\s*CSAPÁGYAK/u');
});

it('shows five items per section and hides the rest behind a working "Összes mutatása"', function (): void {
    foreach (range(1, 7) as $n) {
        Product::factory()->count($n)->create(['size' => "{$n}0X{$n}2X10"]);
    }

    $html = Livewire::test(Index::class)->html();

    expect($html)->toContain('x-data="{ open: true, expanded: false }"')
        ->toContain('@click="expanded = !expanded"')
        ->toContain('Összes mutatása (2)')
        ->and(mb_substr_count($html, 'x-show="expanded"'))->toBe(2);
});

it('finds sizes beyond the most common ones with the size search, comma or point alike', function (): void {
    Product::factory()->count(3)->create(['size' => '25X52X15']);
    Product::factory()->create(['size' => '25,4X50,8X15']);
    Product::factory()->create(['size' => '100X150X12']);

    $component = Livewire::test(Index::class)->set('sizeSearch', '25.4');
    $sizes = collect($component->instance()->filters)->firstWhere('key', 'size')['items'];

    expect(array_column($sizes, 'value'))->toBe(['25,4X50,8X15']);

    $component->set('sizeSearch', 'nincs-ilyen')->assertSee('Nincs ilyen méret.');
});

it('says there is no such size only while searching', function (): void {
    Livewire::test(Index::class)->assertDontSee('Nincs ilyen méret.');
});

it('keeps a ticked size in the list when the size search no longer matches it', function (): void {
    Product::factory()->create(['size' => '25X52X15']);
    Product::factory()->create(['size' => '100X150X12']);

    $component = Livewire::test(Index::class)
        ->set('selectedFilters.size', ['25X52X15'])
        ->set('sizeSearch', '100');
    $sizes = collect($component->instance()->filters)->firstWhere('key', 'size')['items'];

    expect(array_column($sizes, 'value'))->toBe(['25X52X15', '100X150X12']);
});

it('clears the category, size and stock filters and the size search', function (): void {
    Livewire::test(Index::class)
        ->set('selectedFilters.category', ['1'])
        ->set('selectedFilters.size', ['25X52X15'])
        ->set('selectedFilters.stock', ['in_stock'])
        ->set('sizeSearch', '25')
        ->call('clearFilters')
        ->assertSet('selectedFilters', ['category' => [], 'size' => [], 'stock' => []])
        ->assertSet('sizeSearch', '');
});
