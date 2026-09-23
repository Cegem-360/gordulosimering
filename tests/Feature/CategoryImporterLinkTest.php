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

it('makes an uppercase cell a category even when nothing is listed under it', function (): void {
    $path = writeCategoryFixture([
        ['KÖTŐELEMEK', 'CSAVAR', 'HATLAPFEJŰ CSAVAR'],
        ['', '', 'FAG csavar'],
    ]);

    (new CategoryImporter())->importTree($path);

    expect(Category::query()->where('name', 'HATLAPFEJŰ CSAVAR')->exists())->toBeTrue()
        ->and(Category::query()->where('name', 'FAG csavar')->exists())->toBeFalse();
});

it('links products to a multi-word uppercase leaf by its own name, but not to a one-word one', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'SKF VASÚTI ÁGYTOKCSAPÁGY'],
        ['KÖTŐELEMEK', 'ANYA', 'NORMÁL'],
    ]);
    $railway = Product::factory()->create(['name' => 'SKF vasúti ágytokcsapágy 123']);
    $nut = Product::factory()->create(['name' => 'Normál hatlapanya M8']);

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $importer->linkProducts();

    expect($railway->categories()->pluck('name')->all())->toBe(['SKF VASÚTI ÁGYTOKCSAPÁGY'])
        ->and($nut->categories()->count())->toBe(0);
});

it('links a product to every category whose equally long line matches it', function (): void {
    $path = writeCategoryFixture([
        ['KÖTŐELEMEK', 'BILINCS', 'NORMA BENZINCSŐBILINCS', 'NORMA benzincsőbilincs'],
        ['BILINCSEK', 'NORMA BENZINCSŐBILINCS', 'NORMA benzincsőbilincs'],
    ]);
    $clamp = Product::factory()->create(['name' => 'NORMA benzincsőbilincs 9-11/9']);

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $importer->linkProducts();

    expect($clamp->categories()->with('parentCategory')->get()->map(fn (Category $category): string => $category->parentCategory->name)->sort()->values()->all())
        ->toBe(['BILINCS', 'BILINCSEK']);
});

it('moves a product out of the parent it was linked to before its subcategory existed', function (): void {
    $bilincsek = Category::query()->create(['name' => 'BILINCSEK', 'slug' => 'bilincsek']);
    $elsewhere = Category::query()->create(['name' => 'AKCIÓS', 'slug' => 'akcios']);
    $clamp = Product::factory()->create(['name' => 'NORMA benzincsőbilincs 9-11/9']);
    $clamp->categories()->attach([$bilincsek->id, $elsewhere->id]);

    $path = writeCategoryFixture([
        ['BILINCSEK', 'NORMA BENZINCSŐBILINCS', 'NORMA benzincsőbilincs'],
    ]);
    $importer = new CategoryImporter();
    $importer->importTree($path);
    $importer->linkProducts();

    expect($clamp->categories()->pluck('name')->sort()->values()->all())
        ->toBe(['AKCIÓS', 'NORMA BENZINCSŐBILINCS']);
});

it('orders new categories by their row in the sheet and keeps an order set in the admin', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', 'SKF golyóscsapágy'],
        ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', 'SKF görgőscsapágy'],
        ['CSAPÁGYAK', 'GÖMBCSUKLÓ', 'SKF gömbcsukló'],
    ]);
    $importer = new CategoryImporter();
    $importer->importTree($path);

    $root = Category::query()->where('name', 'CSAPÁGYAK')->firstOrFail();
    expect($root->children->pluck('name')->all())->toBe(['GOLYÓS CSAPÁGY', 'GÖRGŐS CSAPÁGY', 'GÖMBCSUKLÓ']);

    Category::query()->where('name', 'GÖMBCSUKLÓ')->update(['sort_order' => 0]);
    $importer->importTree($path);

    expect($root->fresh()->children->pluck('name')->all())->toBe(['GÖMBCSUKLÓ', 'GOLYÓS CSAPÁGY', 'GÖRGŐS CSAPÁGY']);
});

it('links products to the brands named in them as whole words, across categories', function (): void {
    $path = writeCategoryFixture([
        ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', 'FAG golyóscsapágy'],
        ['FORGALMAZOTT MÁRKÁINK', 'FAG'],
        ['', 'INA'],
    ]);
    $fag = Product::factory()->create(['name' => 'FAG golyóscsapágy 6203']);
    $bracketed = Product::factory()->create(['name' => 'Tokés (FAG) csapágy']);
    $laminated = Product::factory()->create(['name' => 'Laminált tömítés']);

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $linked = $importer->linkBrands();

    $fagBrand = Category::query()->where('name', 'FAG')->firstOrFail();
    expect($fagBrand->products()->pluck('products.id')->sort()->values()->all())->toBe([$fag->id, $bracketed->id])
        ->and(Category::query()->where('name', 'INA')->firstOrFail()->products()->count())->toBe(0)
        ->and($laminated->categories()->count())->toBe(0)
        ->and($linked)->toBe(2);
});
