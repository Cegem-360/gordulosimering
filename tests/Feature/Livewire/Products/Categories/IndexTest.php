<?php

declare(strict_types=1);

use App\Livewire\Products\Categories\Index;
use App\Livewire\Products\Index as ProductsIndex;
use App\Models\Category;
use App\Models\DiscountGroup;
use App\Models\Product;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(Index::class)
        ->assertStatus(200);
});

it('offers the stock, category, group, size, brand and material filters but no quality filter', function (string $component): void {
    Livewire::test($component)
        ->assertSee(['Készlet', 'Kategória', 'Termékcsoport', 'Méret', 'Márka', 'Anyag'])
        ->assertDontSee('Minőség');

    expect(collect(Livewire::test($component)->instance()->filters)->pluck('key')->all())
        ->toBe(['stock', 'category', 'group', 'size', 'brand', 'material']);
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
        ->assertSet('selectedFilters', ['category' => [], 'group' => [], 'size' => [], 'brand' => [], 'material' => [], 'stock' => []])
        ->assertSet('sizeSearch', '');
});

it('counts products as in stock by their Integra7 stock, not the minimum stock', function (): void {
    Product::factory()->create(['stock_quantity' => 3, 'minimum_stock' => 0]);
    Product::factory()->create(['stock_quantity' => 0, 'minimum_stock' => 10]);
    Product::factory()->create(['stock_quantity' => 0, 'minimum_stock' => 0]);

    $stockFilter = collect(Livewire::test(ProductsIndex::class)->instance()->filters)->firstWhere('key', 'stock');

    expect(array_column($stockFilter['items'], 'count', 'value'))->toBe(['in_stock' => 1, 'out_of_stock' => 2]);
});

it('lists the brands and materials by how many products have them', function (): void {
    Product::factory()->count(2)->create(['name' => 'SKF simmering, NBR']);
    Product::factory()->create(['name' => 'KOYO simmering, VITON']);
    Product::factory()->create(['name' => 'Gumiházas simmering']);
    Product::factory()->create(['name' => 'INA csapágy', 'is_web_visible' => false]);

    $filters = collect(Livewire::test(Index::class)->instance()->filters);

    expect($filters->firstWhere('key', 'brand')['items'])->toBe([
        ['name' => 'SKF', 'value' => 'SKF', 'count' => 2],
        ['name' => 'KOYO', 'value' => 'KOYO', 'count' => 1],
    ])->and($filters->firstWhere('key', 'material')['items'])->toBe([
        ['name' => 'NBR', 'value' => 'NBR', 'count' => 2],
        ['name' => 'FKM (Viton)', 'value' => 'FKM (Viton)', 'count' => 1],
    ]);
});

it('filters by brand and by material', function (string $key, string $value): void {
    $skfNbr = Product::factory()->create(['name' => 'SKF simmering, NBR']);
    Product::factory()->create(['name' => 'KOYO simmering, VITON']);

    $component = Livewire::test(Index::class)->set("selectedFilters.{$key}", [$value]);

    expect($component->instance()->products->pluck('id')->all())->toBe([$skfNbr->id]);
})->with([
    'brand' => ['brand', 'SKF'],
    'material' => ['material', 'NBR'],
]);

it('merges the product groups that share a name and leaves out the discontinued and unnamed ones', function (): void {
    DiscountGroup::factory()->create(['code' => 'S2', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'S5', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'CT', 'name' => 'Tőkés (minőségi) csapágy']);
    DiscountGroup::factory()->create(['code' => 'PM', 'name' => 'Megszűnt termék']);
    DiscountGroup::factory()->create(['code' => 'XX', 'name' => null]);
    DiscountGroup::factory()->create(['code' => 'EK', 'name' => 'Szíjhajtások']);
    Product::factory()->create(['group_code' => 'S2']);
    Product::factory()->count(2)->create(['group_code' => 'S5']);
    Product::factory()->create(['group_code' => 'CT']);
    Product::factory()->create(['group_code' => 'PM']);
    Product::factory()->create(['group_code' => 'XX']);

    $groups = collect(Livewire::test(Index::class)->instance()->filters)->firstWhere('key', 'group')['items'];

    expect($groups)->toBe([
        ['name' => 'SKF csapágy', 'value' => 'SKF csapágy', 'count' => 3],
        ['name' => 'Tőkés (minőségi) csapágy', 'value' => 'Tőkés (minőségi) csapágy', 'count' => 1],
    ]);
});

it('filters by a product group name across all of its codes and labels the chip with it', function (): void {
    DiscountGroup::factory()->create(['code' => 'S2', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'S5', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'CT', 'name' => 'Tőkés (minőségi) csapágy']);
    $s2 = Product::factory()->create(['group_code' => 'S2', 'name' => 'A']);
    $s5 = Product::factory()->create(['group_code' => 'S5', 'name' => 'B']);
    Product::factory()->create(['group_code' => 'CT']);

    $component = Livewire::test(Index::class)->set('selectedFilters.group', ['SKF csapágy']);

    expect($component->instance()->products->pluck('id')->sort()->values()->all())->toBe([$s2->id, $s5->id])
        ->and($component->html())->toContain('wire:key="chip-group-SKF csapágy"');
});

it('clears the new filters with "Szűrők törlése"', function (): void {
    $component = Livewire::test(Index::class)
        ->set('selectedFilters.brand', ['SKF'])
        ->set('selectedFilters.material', ['NBR'])
        ->set('selectedFilters.group', ['SKF csapágy'])
        ->call('clearFilters');

    expect($component->get('selectedFilters'))
        ->brand->toBe([])
        ->material->toBe([])
        ->group->toBe([]);
});
