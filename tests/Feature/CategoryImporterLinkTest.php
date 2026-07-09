<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryImporter;

it('links each product to its single longest-matching leaf', function (): void {
    $parent = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $generic = Category::query()->create([
        'name' => 'golyóscsapágy',
        'slug' => 'golyoscsapagy',
        'category_id' => $parent->id,
    ]);
    $specific = Category::query()->create([
        'name' => 'egysorú mélyhornyú golyóscsapágy',
        'slug' => 'egysoru-melyhornyu',
        'category_id' => $parent->id,
    ]);

    // A terméknév mindkét levél nevét tartalmazza; a leghosszabb (legspecifikusabb) nyer.
    $match = Product::factory()->create(['name' => 'FAG egysorú mélyhornyú golyóscsapágy 6203']);
    $noMatch = Product::factory()->create(['name' => 'OKS kenőzsír 200ml']);

    $linked = (new CategoryImporter())->linkProducts();

    expect($specific->products()->pluck('products.id')->all())->toBe([$match->id])
        ->and($generic->products()->count())->toBe(0)
        ->and($match->categories()->count())->toBe(1)
        ->and($noMatch->categories()->count())->toBe(0)
        ->and($linked)->toBe(1);
});

it('matches case- and accent-insensitively', function (): void {
    $parent = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $leaf = Category::query()->create(['name' => 'golyóscsapágy', 'slug' => 'golyoscsapagy', 'category_id' => $parent->id]);

    $product = Product::factory()->create(['name' => 'FAG GOLYOSCSAPAGY 6203']);

    (new CategoryImporter())->linkProducts();

    expect($leaf->products()->pluck('products.id')->all())->toBe([$product->id]);
});

it('does not link products to non-leaf (parent) categories', function (): void {
    $parent = Category::query()->create(['name' => 'csapágy', 'slug' => 'csapagy']);
    Category::query()->create(['name' => 'csapágy egysoros', 'slug' => 'csapagy-egysoros', 'category_id' => $parent->id]);
    Product::factory()->create(['name' => 'valami csapágy egysoros termék']);

    (new CategoryImporter())->linkProducts();

    expect($parent->products()->count())->toBe(0);
});

it('does not link products to brand leaves under FORGALMAZOTT MÁRKÁINK', function (): void {
    $brandRoot = Category::query()->create(['name' => 'FORGALMAZOTT MÁRKÁINK', 'slug' => 'forgalmazott-markaink']);
    $skf = Category::query()->create(['name' => 'SKF', 'slug' => 'skf-brand', 'category_id' => $brandRoot->id]);

    Product::factory()->create(['name' => 'SKF egysorú mélyhornyú golyóscsapágy 6203']);

    (new CategoryImporter())->linkProducts();

    expect($skf->products()->count())->toBe(0);
});

it('skips leaves with names shorter than the minimum length', function (): void {
    $parent = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $short = Category::query()->create(['name' => 'EZO', 'slug' => 'ezo-leaf', 'category_id' => $parent->id]);

    Product::factory()->create(['name' => 'EZO egysorú mélyhornyú golyóscsapágy']);

    (new CategoryImporter())->linkProducts();

    expect($short->products()->count())->toBe(0);
});

it('link step is idempotent', function (): void {
    $leaf = Category::query()->create(['name' => 'egyedi termék', 'slug' => 'egyedi-termek']);
    Product::factory()->create(['name' => 'egyedi termék']);

    $importer = new CategoryImporter();
    $importer->linkProducts();

    $first = $leaf->products()->count();
    $importer->linkProducts();

    expect($leaf->products()->count())->toBe($first);
});
