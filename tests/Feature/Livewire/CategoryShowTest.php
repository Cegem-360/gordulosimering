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
