<?php

declare(strict_types=1);

use App\Livewire\Products\Categories\Show;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

it('renders subcategories and aggregates products from the whole subtree', function (): void {
    $root = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $child = Category::query()->create(['name' => 'Golyóscsapágyak', 'slug' => 'golyoscsapagyak', 'category_id' => $root->id]);
    $grandchild = Category::query()->create(['name' => 'Mélyhornyú', 'slug' => 'melyhornyu', 'category_id' => $child->id]);

    $directProduct = Product::factory()->create(['name' => 'Közvetlen csapágy']);
    $deepProduct = Product::factory()->create(['name' => 'Mély csapágy']);
    $root->products()->attach($directProduct);
    $grandchild->products()->attach($deepProduct);

    Livewire::test(Show::class, ['category' => $root])
        ->assertOk()
        ->assertSee('Csapágyak')
        ->assertSee('Golyóscsapágyak')       // subcategory card
        ->assertSee('Közvetlen csapágy')      // product attached to the category itself
        ->assertSee('Mély csapágy');          // product attached to a descendant
});

it('shows an empty state for a leaf category without products', function (): void {
    $category = Category::query()->create(['name' => 'Üres', 'slug' => 'ures']);

    Livewire::test(Show::class, ['category' => $category])
        ->assertOk()
        ->assertSee('Nincs termék ebben a kategóriában');
});

it('resolves the full page route with the slug binding', function (): void {
    /** @var TestCase $this */
    $category = Category::query()->create(['name' => 'Tömítések', 'slug' => 'tomitesek']);

    $this->get(route('categories.show', $category))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});

it('shows only subcategories with products, in menu order, as photo tiles', function (): void {
    showProductImages();

    Storage::fake('public');
    $root = Category::query()->create(['name' => 'BILINCSEK', 'slug' => 'bilincsek']);
    $second = Category::query()->create(['name' => 'NORMA SZORÍTÓBILINCS', 'slug' => 'szorito', 'category_id' => $root->id, 'sort_order' => 2]);
    $first = Category::query()->create(['name' => 'NORMA BENZINCSŐBILINCS', 'slug' => 'benzin', 'category_id' => $root->id, 'sort_order' => 1, 'image' => 'categories/benzin.jpg']);
    Category::query()->create(['name' => 'ÜRES TÍPUS', 'slug' => 'ures-tipus', 'category_id' => $root->id, 'sort_order' => 0]);
    $first->products()->attach(Product::factory()->create());
    $second->products()->attach(Product::factory()->create(['featured_image' => 'products/szorito.jpg']));

    Livewire::test(Show::class, ['category' => $root])
        ->assertSeeInOrder(['NORMA BENZINCSŐBILINCS', 'NORMA SZORÍTÓBILINCS'])
        ->assertDontSee('ÜRES TÍPUS')
        ->assertSeeHtml(Storage::disk('public')->url('categories/benzin.jpg'))
        ->assertSeeHtml(Storage::disk('public')->url('products/szorito.jpg'));
});

it('links back to the parent category, or to all categories from a root', function (): void {
    $root = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $child = Category::query()->create(['name' => 'GOLYÓS CSAPÁGY', 'slug' => 'golyos', 'category_id' => $root->id]);

    Livewire::test(Show::class, ['category' => $child])
        ->assertSee('Vissza: CSAPÁGYAK')
        ->assertSeeHtml(route('categories.show', $root));

    Livewire::test(Show::class, ['category' => $root])
        ->assertSee('Vissza az összes kategóriához');
});

it('lists every product of a brand on its brand page, whatever its category', function (): void {
    $brandRoot = Category::query()->create(['name' => Category::BRAND_ROOT_NAME, 'slug' => 'markak']);
    $skf = Category::query()->create(['name' => 'SKF', 'slug' => 'skf', 'category_id' => $brandRoot->id]);
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $grease = Category::query()->create(['name' => 'ZSÍRZÁSTECHNIKA', 'slug' => 'zsir']);
    $bearing = Product::factory()->create(['name' => 'SKF golyóscsapágy 6203']);
    $lubricant = Product::factory()->create(['name' => 'SKF kenőzsír LGMT 2']);
    $bearings->products()->attach($bearing);
    $grease->products()->attach($lubricant);
    $skf->products()->attach([$bearing->id, $lubricant->id]);

    Livewire::test(Show::class, ['category' => $skf])
        ->assertSee('SKF golyóscsapágy 6203')
        ->assertSee('SKF kenőzsír LGMT 2');
});

it('writes the product count with a Hungarian thousands separator', function (): void {
    $category = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $products = Product::factory()->count(1001)->create();
    $category->products()->attach($products->modelKeys());

    Livewire::test(Show::class, ['category' => $category])
        ->assertSee("1\u{a0}001 termék található")
        ->assertDontSee('1,001 termék');
});

it('offers the filter sidebar without the category filter, counted within the category', function (): void {
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $seals->products()->attach(Product::factory()->count(2)->create(['name' => 'SKF simmering, NBR', 'size' => '25X47X8']));
    $bearings->products()->attach(Product::factory()->create(['name' => 'KOYO golyóscsapágy', 'size' => '30X62X16']));

    $component = Livewire::test(Show::class, ['category' => $seals]);
    $filters = collect($component->instance()->filters);

    expect($filters->pluck('key')->all())->toBe(['stock', 'group', 'dimensions', 'size', 'brand', 'material'])
        ->and($filters->firstWhere('key', 'brand')['items'])->toBe([['name' => 'SKF', 'value' => 'SKF', 'count' => 2]])
        ->and($filters->firstWhere('key', 'dimensions')['ranges'][0])->toMatchArray(['min' => 25.0, 'max' => 25.0]);
    $component->assertSee(['Márka', 'Anyag', 'Méretek (mm)'])
        ->assertSeeHtml('wire:model.live="selectedFilters.brand"');
});

it('narrows the category products by brand, material and dimension range', function (string $property, mixed $value): void {
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $seals->products()->attach($match = Product::factory()->create(['name' => 'SKF simmering, NBR', 'size' => '25X47X8']));
    $seals->products()->attach(Product::factory()->create(['name' => 'CORTECO simmering, VITON', 'size' => '40X62X10']));
    Product::factory()->create(['name' => 'SKF simmering, NBR', 'size' => '25X47X8']);

    $component = Livewire::test(Show::class, ['category' => $seals])->set($property, $value);

    expect($component->instance()->products->pluck('id')->all())->toBe([$match->id]);
})->with([
    'brand' => ['selectedFilters.brand', ['SKF']],
    'material' => ['selectedFilters.material', ['NBR']],
    'range' => ['dimensionRanges.inner_diameter', ['min' => '20', 'max' => '30']],
]);

it('shows the active filter chips and a way out when the filters match nothing', function (): void {
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $seals->products()->attach(Product::factory()->create(['name' => 'SKF simmering, NBR', 'size' => '25X47X8']));

    Livewire::test(Show::class, ['category' => $seals])
        ->set('selectedFilters.brand', ['SKF'])
        ->set('dimensionRanges.width.min', '50')
        ->assertSeeHtml('wire:key="chip-brand-SKF"')
        ->assertSee('Szélesség: 50 mm-től')
        ->assertSee('A megadott szűrőkkel nem található termék.')
        ->assertDontSee('Nincs termék ebben a kategóriában')
        ->call('clearFilters')
        ->assertSet('selectedFilters.brand', [])
        ->assertSet('dimensionRanges.width', ['min' => null, 'max' => null])
        ->assertSee('SKF simmering, NBR');
});
