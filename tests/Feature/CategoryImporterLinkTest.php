<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryImporter;

/**
 * Writes a 5-column category fixture (rows of cells) to a temp TSV.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function writeCategoryFixture(array $rows): string
{
    $lines = [];
    foreach ($rows as $row) {
        $cells = array_pad($row, 5, '');
        $lines[] = mb_rtrim(implode("\t", $cells), "\t");
    }

    $path = tempnam(sys_get_temp_dir(), 'kat_') . '.tsv';
    file_put_contents($path, implode("\n", $lines) . "\n");

    return $path;
}

afterEach(function (): void {
    foreach (glob(sys_get_temp_dir() . '/kat_*.tsv') ?: [] as $file) {
        @unlink($file);
    }
});

it('builds the tree from internal nodes only, leaves are not categories', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', 'FAG egysorú mélyhornyú golyóscsapágy'],
        ['', '', 'SKF egysorú mélyhornyú golyóscsapágy'],
        ['CSAPÁGYAK', 'TŰGÖRGŐS CSAPÁGY', 'INA tűgörgős csapágy'],
    ]);

    $importer = new CategoryImporter();
    $importer->importTree($path);

    // Only internal nodes become categories: CSAPÁGYAK, GOLYÓS CSAPÁGY, TŰGÖRGŐS CSAPÁGY
    expect(Category::query()->count())->toBe(3)
        ->and(Category::query()->whereNull('category_id')->pluck('name')->all())->toBe(['CSAPÁGYAK'])
        ->and(Category::query()->where('name', 'FAG egysorú mélyhornyú golyóscsapágy')->exists())->toBeFalse()
        ->and(Category::query()->where('name', 'GOLYÓS CSAPÁGY')->exists())->toBeTrue();
});

it('links products to the parent category of their matching product line', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', 'FAG egysorú mélyhornyú golyóscsapágy'],
        ['CSAPÁGYAK', 'TŰGÖRGŐS CSAPÁGY', 'INA tűgörgős csapágy'],
    ]);

    $golyos = Product::factory()->create(['name' => 'FAG egysorú mélyhornyú golyóscsapágy 6203-2RS']);
    $tu = Product::factory()->create(['name' => 'INA tűgörgős csapágy NK 12/16']);
    $noMatch = Product::factory()->create(['name' => 'OKS kenőzsír 200ml']);

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $linked = $importer->linkProducts();

    $golyosCat = Category::query()->where('name', 'GOLYÓS CSAPÁGY')->first();
    $tuCat = Category::query()->where('name', 'TŰGÖRGŐS CSAPÁGY')->first();

    expect($golyosCat->products()->pluck('products.id')->all())->toBe([$golyos->id])
        ->and($tuCat->products()->pluck('products.id')->all())->toBe([$tu->id])
        ->and($noMatch->categories()->count())->toBe(0)
        ->and($linked)->toBe(2);
});

it('matches case- and accent-insensitively', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', 'FAG mélyhornyú golyóscsapágy'],
    ]);

    $product = Product::factory()->create(['name' => 'FAG MELYHORNYU GOLYOSCSAPAGY 6203']);

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $importer->linkProducts();

    $cat = Category::query()->where('name', 'GOLYÓS CSAPÁGY')->first();
    expect($cat->products()->pluck('products.id')->all())->toBe([$product->id]);
});

it('does not link products via brand names under FORGALMAZOTT MÁRKÁINK', function (): void {
    $path = writeCategoryFixture([
        ['FORGALMAZOTT MÁRKÁINK', 'SKF'],
    ]);

    Product::factory()->create(['name' => 'SKF egysorú mélyhornyú golyóscsapágy 6203']);

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $linked = $importer->linkProducts();

    expect($linked)->toBe(0);
});

it('the longest matching product line wins', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'ÁLTALÁNOS', 'golyóscsapágy'],
        ['CSAPÁGYAK', 'SPECIÁLIS', 'rozsdamentes egysorú mélyhornyú golyóscsapágy'],
    ]);

    $product = Product::factory()->create(['name' => 'FAG rozsdamentes egysorú mélyhornyú golyóscsapágy 6203']);

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $importer->linkProducts();

    $special = Category::query()->where('name', 'SPECIÁLIS')->first();
    $general = Category::query()->where('name', 'ÁLTALÁNOS')->first();

    expect($special->products()->pluck('products.id')->all())->toBe([$product->id])
        ->and($general->products()->count())->toBe(0);
});

it('link step is idempotent', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', 'FAG mélyhornyú golyóscsapágy'],
    ]);

    Product::factory()->create(['name' => 'FAG mélyhornyú golyóscsapágy 6203']);

    $importer = new CategoryImporter();
    $importer->importTree($path);

    $first = $importer->linkProducts();
    $second = $importer->linkProducts();

    $cat = Category::query()->where('name', 'GOLYÓS CSAPÁGY')->first();

    expect($first)->toBe(1)
        ->and($second)->toBe(0)
        ->and($cat->products()->count())->toBe(1);
});
