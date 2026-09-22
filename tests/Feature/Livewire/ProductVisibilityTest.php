<?php

declare(strict_types=1);

use App\Livewire\CartItem;
use App\Livewire\LiveSearch;
use App\Livewire\Products\Categories\Index as CategoriesIndex;
use App\Livewire\Products\Categories\Show as CategoriesShow;
use App\Livewire\Products\Index as ProductsIndex;
use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;
use Tests\TestCase;

function visibleProduct(array $attributes = []): Product
{
    return Product::factory()->create([...$attributes, 'is_web_visible' => true]);
}

function hiddenProduct(array $attributes = []): Product
{
    return Product::factory()->create([...$attributes, 'is_web_visible' => false]);
}

it('returns 404 for the product page of a product that is not web visible', function (): void {
    /** @var TestCase $this */
    $product = hiddenProduct(['slug' => 'rejtett-termek', 'name' => 'Rejtett termék']);

    $this->get(route('products.show', $product->slug))->assertNotFound();
});

it('still serves the product page of a web visible product', function (): void {
    /** @var TestCase $this */
    $product = visibleProduct(['slug' => 'lathato-termek', 'name' => 'Látható termék']);

    $this->get(route('products.show', $product->slug))->assertSuccessful();
});

it('keeps products that are not web visible out of the product list', function (): void {
    visibleProduct(['name' => 'Latszik Ez', 'product_code' => 'LATSZIK-1']);
    hiddenProduct(['name' => 'Rejtve Ez', 'product_code' => 'REJTVE-1']);

    $products = Livewire::test(ProductsIndex::class)->get('products');

    expect($products->pluck('product_code')->all())->toBe(['LATSZIK-1']);
});

it('keeps products that are not web visible out of the live search results', function (): void {
    visibleProduct(['name' => 'Csapagy latszik', 'product_code' => 'KERES-LAT']);
    hiddenProduct(['name' => 'Csapagy rejtve', 'product_code' => 'KERES-REJT']);

    $results = Livewire::test(LiveSearch::class)->set('query', 'KERES')->get('results');

    expect($results->pluck('product_code')->all())->toBe(['KERES-LAT']);
});

it('keeps products that are not web visible out of the category page', function (): void {
    $category = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $category->products()->attach(visibleProduct(['product_code' => 'KAT-LAT']));
    $category->products()->attach(hiddenProduct(['product_code' => 'KAT-REJT']));

    $products = Livewire::test(CategoriesShow::class, ['category' => $category])->get('products');

    expect($products->pluck('product_code')->all())->toBe(['KAT-LAT']);
});

it('keeps products that are not web visible out of the category index list', function (): void {
    visibleProduct(['product_code' => 'IDX-LAT']);
    hiddenProduct(['product_code' => 'IDX-REJT']);

    $products = Livewire::test(CategoriesIndex::class)->get('products');

    expect($products->pluck('product_code')->all())->toBe(['IDX-LAT']);
});

it('keeps products that are not web visible off the home page', function (): void {
    /** @var TestCase $this */
    visibleProduct(['name' => 'Kiemelt Lathato Termek']);
    hiddenProduct(['name' => 'Kiemelt Rejtett Termek']);

    $this->get(route('index'))
        ->assertSee('Kiemelt Lathato Termek')
        ->assertDontSee('Kiemelt Rejtett Termek');
});

it('still resolves a cart item whose product became hidden after the order', function (): void {
    $product = hiddenProduct(['name' => 'Kosarban maradt']);

    $component = Livewire::test(CartItem::class, [
        'productId' => $product->id,
        'quantity' => 1,
    ]);

    expect($component->get('product')->id)->toBe($product->id);
});
