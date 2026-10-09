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
        ->assertSee(['Készlet', 'Kategória', 'Termékcsoport', 'Méretek (mm)', 'Méret', 'Márka', 'Anyag'])
        ->assertDontSee('Minőség');

    expect(collect(Livewire::test($component)->instance()->filters)->pluck('key')->all())
        ->toBe(['stock', 'category', 'group', 'dimensions', 'size', 'brand', 'material']);
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

it('clears every filter and the size search', function (): void {
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

it('filters by brand and by material', function (string $component, string $key, string $value): void {
    $skfNbr = Product::factory()->create(['name' => 'SKF simmering, NBR']);
    Product::factory()->create(['name' => 'KOYO simmering, VITON']);

    $component = Livewire::test($component)->set("selectedFilters.{$key}", [$value]);

    expect($component->instance()->products->pluck('id')->all())->toBe([$skfNbr->id]);
})->with([
    'category index brand' => [Index::class, 'brand', 'SKF'],
    'category index material' => [Index::class, 'material', 'NBR'],
    'product list brand' => [ProductsIndex::class, 'brand', 'SKF'],
    'product list material' => [ProductsIndex::class, 'material', 'NBR'],
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

it('filters by a product group name across all of its codes and labels the chip with it', function (string $component): void {
    DiscountGroup::factory()->create(['code' => 'S2', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'S5', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'CT', 'name' => 'Tőkés (minőségi) csapágy']);
    $s2 = Product::factory()->create(['group_code' => 'S2', 'name' => 'A']);
    $s5 = Product::factory()->create(['group_code' => 'S5', 'name' => 'B']);
    Product::factory()->create(['group_code' => 'CT']);

    $test = Livewire::test($component)->set('selectedFilters.group', ['SKF csapágy']);

    expect($test->instance()->products->pluck('id')->sort()->values()->all())->toBe([$s2->id, $s5->id])
        ->and($test->html())->toContain('wire:key="chip-group-SKF csapágy"');
})->with([
    'category index' => [Index::class],
    'product list' => [ProductsIndex::class],
]);

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

it('filters by the exact size of each dimension', function (string $component, string $dimension, string $value, array $expectedSizes): void {
    Product::factory()->create(['size' => '20X47X14']);
    Product::factory()->create(['size' => '25X52X15']);
    Product::factory()->create(['size' => '25X62X17']);
    Product::factory()->create(['size' => 'A28,5']);

    $component = Livewire::test($component)->set("dimensions.{$dimension}", $value);

    expect($component->instance()->products->pluck('size')->sort()->values()->all())->toBe($expectedSizes);
})->with([
    'inner diameter' => [Index::class, 'inner_diameter', '25', ['25X52X15', '25X62X17']],
    'outer diameter' => [Index::class, 'outer_diameter', '52', ['25X52X15']],
    'width' => [Index::class, 'width', '17', ['25X62X17']],
    'no match' => [Index::class, 'inner_diameter', '24', []],
    'product list' => [ProductsIndex::class, 'inner_diameter', '20', ['20X47X14']],
]);

it('combines the three sizes', function (): void {
    Product::factory()->create(['size' => '25X52X15']);
    Product::factory()->create(['size' => '25X52X18']);
    Product::factory()->create(['size' => '25X62X15']);

    $component = Livewire::test(Index::class)
        ->set('dimensions.inner_diameter', '25')
        ->set('dimensions.outer_diameter', '52')
        ->set('dimensions.width', '15');

    expect($component->instance()->products->pluck('size')->all())->toBe(['25X52X15']);
});

it('matches a decimal size given with a comma or a point, and only that size', function (string $value): void {
    Product::factory()->create(['size' => '17X40X12']);
    Product::factory()->create(['size' => '17,5X40X12']);
    Product::factory()->create(['size' => '17,55X40X12']);

    $component = Livewire::test(Index::class)->set('dimensions.inner_diameter', $value);

    expect($component->instance()->products->pluck('size')->all())->toBe(['17,5X40X12']);
})->with(['decimal comma' => '17,5', 'decimal point' => '17.5', 'trailing zero' => '17,50']);

it('ignores a size that is not a non-negative number', function (string $value): void {
    Product::factory()->count(2)->create(['size' => '25X52X15']);

    expect(Livewire::test(Index::class)->set('dimensions.inner_diameter', $value)->instance()->products)->toHaveCount(2);
})->with(['text' => 'abc', 'negative' => '-5', 'empty' => '']);

it('offers one text field per dimension so a decimal comma reaches the server in every browser', function (): void {
    Product::factory()->create(['size' => '20X47X14']);

    $component = Livewire::test(Index::class);
    $dimensions = collect($component->instance()->filters)->firstWhere('key', 'dimensions');

    expect($dimensions['fields'])->toBe([
        ['key' => 'inner_diameter', 'label' => 'Belső átmérő (d)'],
        ['key' => 'outer_diameter', 'label' => 'Külső átmérő (D)'],
        ['key' => 'width', 'label' => 'Szélesség (B)'],
    ])
        ->and(mb_substr_count($component->html(), 'type="text" inputmode="decimal"'))->toBe(3)
        ->and($component->html())
        ->toContain('wire:model.live.debounce.500ms="dimensions.inner_diameter"')
        ->not->toContain('type="number"')
        ->not->toContain('dimensions.inner_diameter.min');
});

it('goes back to the first page when a size changes', function (): void {
    Product::factory()->count(30)->create(['size' => '25X52X15']);

    Livewire::test(Index::class)
        ->call('gotoPage', 2)
        ->set('dimensions.width', '15')
        ->assertSet('paginators.page', 1);
});

it('shows a removable chip for each size and clears the sizes with "Szűrők törlése"', function (string $component): void {
    $test = Livewire::test($component)
        ->set('dimensions.inner_diameter', '20')
        ->set('dimensions.outer_diameter', '62,5')
        ->set('dimensions.width', '10');

    expect($test->instance()->dimensionChips)->toBe([
        ['key' => 'inner_diameter', 'label' => 'Belső átmérő: 20 mm'],
        ['key' => 'outer_diameter', 'label' => 'Külső átmérő: 62,5 mm'],
        ['key' => 'width', 'label' => 'Szélesség: 10 mm'],
    ]);
    $test->assertSee('Belső átmérő: 20 mm')->assertSeeHtml('wire:click="clearDimension(\'width\')"');

    $test->call('clearDimension', 'width')
        ->assertSet('dimensions.width', null)
        ->call('clearFilters')
        ->assertSet('dimensions', ['inner_diameter' => null, 'outer_diameter' => null, 'width' => null]);
})->with([
    'category index' => [Index::class],
    'product list' => [ProductsIndex::class],
]);

it('keeps working with the filter state of a page opened before the attribute filters existed', function (string $component): void {
    $skf = Product::factory()->create(['name' => 'SKF simmering, NBR']);
    Product::factory()->create(['name' => 'KOYO simmering, VITON']);

    $test = Livewire::test($component)
        ->set('selectedFilters', ['category' => [], 'size' => [], 'stock' => []])
        ->set('dimensions', [])
        ->set('selectedFilters.brand', ['SKF']);

    expect($test->instance()->products->pluck('id')->all())->toBe([$skf->id]);

    $test->set('dimensions.width', '5')->assertOk();
})->with([
    'category index' => [Index::class],
    'product list' => [ProductsIndex::class],
]);
