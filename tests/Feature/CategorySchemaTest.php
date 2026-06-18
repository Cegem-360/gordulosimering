<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

it('builds parent-child category relations on product_categories table', function () {
    $parent = Category::create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $child = Category::create(['name' => 'Golyós', 'slug' => 'golyos', 'category_id' => $parent->id]);

    expect($child->parentCategory->id)->toBe($parent->id)
        ->and($parent->children->pluck('id')->all())->toBe([$child->id]);

    expect(Schema::hasTable('product_categories'))->toBeTrue();
});

it('attaches products to a category via the pivot', function () {
    $category = Category::create(['name' => 'Lev', 'slug' => 'lev']);
    $product = Product::factory()->create();

    $category->products()->attach($product->id);

    expect($category->products()->count())->toBe(1);
});
