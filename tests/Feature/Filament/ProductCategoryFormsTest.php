<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('renders the reworked product create and edit forms', function (): void {
    $product = Product::factory()->create();

    get('admin/products/create')->assertSuccessful();
    get('admin/products/' . $product->getKey() . '/edit')->assertSuccessful();
    get('admin/products/' . $product->getKey())->assertSuccessful();
});

it('renders the reworked category create and edit forms', function (): void {
    $category = Category::query()->create(['name' => 'Teszt', 'slug' => 'teszt']);

    get('admin/categories/create')->assertSuccessful();
    get('admin/categories/' . $category->getKey() . '/edit')->assertSuccessful();
});

it('persists product image, document and category assignments', function (): void {
    $category = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $product = Product::factory()->create([
        'images' => ['products/images/a.jpg'],
        'documents' => ['products/documents/datasheet.pdf'],
    ]);
    $product->categories()->attach($category);

    expect($product->fresh()->images)->toBe(['products/images/a.jpg'])
        ->and($product->fresh()->documents)->toBe(['products/documents/datasheet.pdf'])
        ->and($product->categories()->pluck('name')->all())->toBe(['Csapágyak']);
});
