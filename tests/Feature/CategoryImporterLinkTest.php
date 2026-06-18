<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryImporter;

it('links products to leaf categories by substring, both directions', function () {
    $parent = Category::create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $leaf = Category::create([
        'name' => 'egysorú mélyhornyú golyóscsapágy',
        'slug' => 'egysoru-melyhornyu',
        'category_id' => $parent->id,
    ]);
    $exactLeaf = Category::create([
        'name' => 'DURACELL 9V-os elem',
        'slug' => 'duracell-9v',
        'category_id' => $parent->id,
    ]);

    // A: terméknév TARTALMAZZA a levél nevét
    $match = Product::factory()->create(['name' => 'FAG egysorú mélyhornyú golyóscsapágy 6203']);
    // Nem illeszkedő
    $noMatch = Product::factory()->create(['name' => 'OKS kenőzsír 200ml']);
    // B: a levél neve TARTALMAZZA a terméknevet (rövidebb terméknév)
    $reverse = Product::factory()->create(['name' => 'DURACELL 9V-os elem']);

    $linked = (new CategoryImporter())->linkProducts();

    expect($leaf->products()->pluck('products.id')->all())->toBe([$match->id])
        ->and($exactLeaf->products()->pluck('products.id')->all())->toBe([$reverse->id])
        ->and($noMatch->categories ?? collect())->toHaveCount(0)
        ->and($linked)->toBeGreaterThan(0);
});

it('does not link products to non-leaf (parent) categories', function () {
    $parent = Category::create(['name' => 'csapágy', 'slug' => 'csapagy']);
    Category::create(['name' => 'csapágy egysoros', 'slug' => 'csapagy-egysoros', 'category_id' => $parent->id]);
    Product::factory()->create(['name' => 'valami csapágy egysoros termék']);

    (new CategoryImporter())->linkProducts();

    expect($parent->products()->count())->toBe(0);
});

it('link step is idempotent', function () {
    $leaf = Category::create(['name' => 'egyedi termék', 'slug' => 'egyedi-termek']);
    Product::factory()->create(['name' => 'egyedi termék']);

    $importer = new CategoryImporter();
    $importer->linkProducts();
    $first = $leaf->products()->count();
    $importer->linkProducts();

    expect($leaf->products()->count())->toBe($first);
});
