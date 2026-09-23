<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryTree;
use Illuminate\Support\Facades\Storage;

it('treats a category as stocked when a web-visible product sits anywhere below it', function (): void {
    $root = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $child = Category::query()->create(['name' => 'GOLYÓS', 'slug' => 'golyos', 'category_id' => $root->id]);
    $grandchild = Category::query()->create(['name' => 'MÉLYHORNYÚ', 'slug' => 'melyhornyu', 'category_id' => $child->id]);
    $hiddenOnly = Category::query()->create(['name' => 'REJTETT', 'slug' => 'rejtett', 'category_id' => $root->id]);
    $empty = Category::query()->create(['name' => 'ÜRES', 'slug' => 'ures']);
    $grandchild->products()->attach(Product::factory()->create());
    $hiddenOnly->products()->attach(Product::factory()->create(['is_web_visible' => false]));

    $tree = new CategoryTree();

    expect($tree->isStocked($root))->toBeTrue()
        ->and($tree->isStocked($child))->toBeTrue()
        ->and($tree->isStocked($grandchild))->toBeTrue()
        ->and($tree->isStocked($hiddenOnly))->toBeFalse()
        ->and($tree->isStocked($empty))->toBeFalse()
        ->and($tree->stocked(collect([$empty, $root, $hiddenOnly]))->pluck('name')->all())->toBe(['CSAPÁGYAK'])
        ->and($tree->descendantIds($root))->toEqualCanonicalizing([$root->id, $child->id, $grandchild->id, $hiddenOnly->id]);
});

it('uses the uploaded category photo, then the first product picture below, then nothing', function (): void {
    Storage::fake('public');
    $withPhoto = Category::query()->create(['name' => 'A', 'slug' => 'a', 'image' => 'categories/a.jpg']);
    $root = Category::query()->create(['name' => 'B', 'slug' => 'b']);
    $child = Category::query()->create(['name' => 'B1', 'slug' => 'b1', 'category_id' => $root->id]);
    $child->products()->attach([
        Product::factory()->create(['name' => 'Aaa kép nélkül', 'featured_image' => null])->id,
        Product::factory()->create(['name' => 'Bbb képpel', 'featured_image' => 'products/bbb.jpg'])->id,
    ]);
    $noPicture = Category::query()->create(['name' => 'C', 'slug' => 'c']);

    $tree = new CategoryTree();

    expect($tree->coverImageUrl($withPhoto))->toBe(Storage::disk('public')->url('categories/a.jpg'))
        ->and($tree->coverImageUrl($root))->toBe(Storage::disk('public')->url('products/bbb.jpg'))
        ->and($tree->coverImageUrl($noPicture))->toBeNull();
});
