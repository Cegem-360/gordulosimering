<?php

declare(strict_types=1);

use App\Livewire\Products\Categories\Show;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

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
    $category = Category::query()->create(['name' => 'Tömítések', 'slug' => 'tomitesek']);

    $this->get(route('categories.show', $category))
        ->assertOk()
        ->assertSeeLivewire(Show::class);
});
