<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\ProductImageImporter;
use Illuminate\Support\Facades\File;

/**
 * @param  array<int, array<int, string>>  $rows  each: [code, name, kep1, kep2, kep3]
 */
function writeImageFixture(array $rows): string
{
    $lines = ["TERMÉKKÓD\tTERMÉKNÉV\tKÉP 1\tKÉP 2\tKÉP 3"];
    foreach ($rows as $row) {
        $lines[] = implode("\t", array_pad($row, 5, ''));
    }

    $path = tempnam(sys_get_temp_dir(), 'kep_') . '.tsv';
    file_put_contents($path, implode("\n", $lines) . "\n");

    return $path;
}

afterEach(function (): void {
    foreach (glob(sys_get_temp_dir() . '/kep_*.tsv') ?: [] as $file) {
        File::delete($file);
    }
});

it('assigns the featured image and gallery on an exact code match', function (): void {
    $product = Product::factory()->create(['product_code' => 'BETA 7399AN/100', 'featured_image' => null, 'images' => null]);

    $path = writeImageFixture([
        ['BETA 7399AN/100', 'BETA cipőfűző', 'https://cdn.test/a.jpg', 'https://cdn.test/b.jpg', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    $product->refresh();
    expect($product->featured_image)->toBe('https://cdn.test/a.jpg')
        ->and($product->images)->toBe(['https://cdn.test/b.jpg'])
        ->and($stats['exact'])->toBe(1)
        ->and($stats['products'])->toBe(1);
});

it('assigns a group image to all size variants via the "..." wildcard', function (): void {
    $v38 = Product::factory()->create(['product_code' => 'BETA 7352B/38']);
    $v39 = Product::factory()->create(['product_code' => 'BETA 7352B/39']);
    $other = Product::factory()->create(['product_code' => 'BETA 9999/10']);

    $path = writeImageFixture([
        ['BETA 7352B ...', 'BETA cipő', 'https://cdn.test/shoe.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    expect($v38->fresh()->featured_image)->toBe('https://cdn.test/shoe.jpg')
        ->and($v39->fresh()->featured_image)->toBe('https://cdn.test/shoe.jpg')
        ->and($other->fresh()->featured_image)->toBeNull()
        ->and($stats['wildcard'])->toBe(1)
        ->and($stats['products'])->toBe(2);
});

it('skips over-generic codes that match more than the max variants', function (): void {
    Product::factory()->count(4)->sequence(
        ['product_code' => 'N100'],
        ['product_code' => 'N200'],
        ['product_code' => 'N300'],
        ['product_code' => 'N400'],
    )->create(['featured_image' => null]);

    $path = writeImageFixture([
        ['N ...', 'Generic', 'https://cdn.test/x.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path, maxVariantsPerCode: 3);

    expect($stats['skipped'])->toBe(1)
        ->and($stats['products'])->toBe(0)
        ->and(Product::query()->whereNotNull('featured_image')->count())->toBe(0)
        ->and($stats['skipped_codes'])->toHaveKey('N ...');
});

it('ignores codes not present in the catalogue and rows without images', function (): void {
    Product::factory()->create(['product_code' => 'EXISTS/1']);

    $path = writeImageFixture([
        ['NOSUCH/1', 'Missing', 'https://cdn.test/x.jpg', '', ''],
        ['EXISTS/1', 'No image row', '', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    expect($stats['zero'])->toBe(1)
        ->and($stats['codes'])->toBe(1)
        ->and($stats['products'])->toBe(0);
});

it('does not let a bare code prefix run into a longer number or word', function (): void {
    $bare = Product::factory()->create(['product_code' => 'LOC 270 50ML', 'featured_image' => null]);
    $longerNumber = Product::factory()->create(['product_code' => 'LOC 2701 50ML', 'featured_image' => null]);
    $series = Product::factory()->create(['product_code' => 'KRE16_S', 'featured_image' => null]);
    $longerWord = Product::factory()->create(['product_code' => 'KRESZ TÁBLA', 'featured_image' => null]);

    $path = writeImageFixture([
        ['LOC 270', 'LOCTITE 270', 'https://cdn.test/270.jpg', '', ''],
        ['KRE', 'SKF támasztógörgő', 'https://cdn.test/kre.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    expect($bare->fresh()->featured_image)->toBe('https://cdn.test/270.jpg')
        ->and($longerNumber->fresh()->featured_image)->toBeNull()
        ->and($series->fresh()->featured_image)->toBe('https://cdn.test/kre.jpg')
        ->and($longerWord->fresh()->featured_image)->toBeNull()
        ->and($stats['products'])->toBe(2);
});

it('still lets an explicit "..." wildcard match a longer code', function (): void {
    $product = Product::factory()->create(['product_code' => 'S7014 CEGA/P4A_S', 'featured_image' => null]);

    $path = writeImageFixture([
        ['S7 ...', 'SKF ferde hatásvonalú golyóscsapágy', 'https://cdn.test/s7.jpg', '', ''],
    ]);

    (new ProductImageImporter())->import($path);

    expect($product->fresh()->featured_image)->toBe('https://cdn.test/s7.jpg');
});

it('clears every imported image first on a fresh import, but keeps admin uploads', function (): void {
    $hash = str_repeat('a', 64);
    $stale = Product::factory()->create([
        'product_code' => 'KIKERULT/1',
        'featured_image' => "products/{$hash}.jpg",
        'images' => ["products/{$hash}.png", 'products/images/admin-galeria.jpg'],
    ]);
    $remote = Product::factory()->create(['product_code' => 'KULSO/1', 'featured_image' => 'https://cdn.test/old.jpg', 'images' => null]);
    $uploaded = Product::factory()->create(['product_code' => 'ADMIN/1', 'featured_image' => 'products/featured/admin.jpg', 'images' => null]);
    $listed = Product::factory()->create(['product_code' => 'LISTAN/1', 'featured_image' => "products/{$hash}.jpg", 'images' => null]);

    $path = writeImageFixture([
        ['LISTAN/1', 'Listán', 'https://cdn.test/new.jpg', '', ''],
    ]);

    $stats = (new ProductImageImporter())->import($path, fresh: true);

    expect($stale->fresh()->featured_image)->toBeNull()
        ->and($stale->fresh()->images)->toBe(['products/images/admin-galeria.jpg'])
        ->and($remote->fresh()->featured_image)->toBeNull()
        ->and($uploaded->fresh()->featured_image)->toBe('products/featured/admin.jpg')
        ->and($listed->fresh()->featured_image)->toBe('https://cdn.test/new.jpg')
        ->and($stats['cleared'])->toBe(3);
});

it('keeps existing images when the import is not fresh', function (): void {
    $product = Product::factory()->create([
        'product_code' => 'KIKERULT/1',
        'featured_image' => 'products/' . str_repeat('b', 64) . '.jpg',
    ]);

    (new ProductImageImporter())->import(writeImageFixture([]));

    expect($product->fresh()->featured_image)->toBe('products/' . str_repeat('b', 64) . '.jpg');
});

it('splits an image cell holding several URLs joined by "||"', function (): void {
    $product = Product::factory()->create(['product_code' => 'SY 505 M', 'featured_image' => null, 'images' => null]);

    $path = writeImageFixture([
        ['SY 505 M', 'SKF Y csapágyház', 'https://cdn.test/a.png', 'https://cdn.test/b.svg||https://cdn.test/c.svg', ''],
    ]);

    (new ProductImageImporter())->import($path);

    expect($product->fresh()->featured_image)->toBe('https://cdn.test/a.png')
        ->and($product->fresh()->images)->toBe(['https://cdn.test/b.svg', 'https://cdn.test/c.svg']);
});

it('gives every SKF product of the listed series the family image', function (): void {
    $covered = collect(['6205-2Z_S', '6000_S', '6299/C3_S', '16004_S', 'BB1-3055_S', 'NU 2207 ECJ_S'])
        ->map(fn (string $code): Product => Product::factory()->create(['product_code' => $code, 'featured_image' => null]));
    $notCovered = collect(['62205-2RS1_S', '6205-2Z', '6205 KOYO', 'NUP 306 ECP_S', '7205 BECBP_S'])
        ->map(fn (string $code): Product => Product::factory()->create(['product_code' => $code, 'featured_image' => null]));

    $path = writeImageFixture([
        ['6000, 6200, 16000, BB1-', 'SKF egysorú mélyhornyú golyóscsapágy', 'https://cdn.test/deep-groove.png'],
        ['NU', 'SKF egysorú hengergörgős csapágy (NU)', 'https://cdn.test/nu.png'],
        ['NJ, NU', 'SKF egysorú hengergörgős csapágy', 'https://cdn.test/nj-nu.png'],
    ]);

    $stats = (new ProductImageImporter())->import($path);

    foreach ($covered->take(5) as $product) {
        expect($product->fresh()->featured_image)->toBe('https://cdn.test/deep-groove.png');
    }

    expect($covered->last()->fresh()->featured_image)->toBe('https://cdn.test/nu.png')
        ->and($notCovered->every(fn (Product $product): bool => $product->fresh()->featured_image === null))->toBeTrue()
        ->and($stats['products'])->toBe(7);
});

it('applies the wildcard suffix of the last listed code to every code of the list', function (): void {
    $tapered = Product::factory()->create(['product_code' => '22217 EK/C3_S', 'featured_image' => null]);
    $alsoTapered = Product::factory()->create(['product_code' => '23120 CCK/W33_S', 'featured_image' => null]);
    $cylindrical = Product::factory()->create(['product_code' => '22217 E/C3_S', 'featured_image' => null]);

    $path = writeImageFixture([
        ['22200, 23100 ... K', 'SKF beálló görgőscsapágy kúpos furattal', 'https://cdn.test/tapered.png'],
    ]);

    (new ProductImageImporter())->import($path);

    expect($tapered->fresh()->featured_image)->toBe('https://cdn.test/tapered.png')
        ->and($alsoTapered->fresh()->featured_image)->toBe('https://cdn.test/tapered.png')
        ->and($cylindrical->fresh()->featured_image)->toBeNull();
});

it('lets a narrower code overwrite the series image wherever the sheet puts it', function (): void {
    $plain = Product::factory()->create(['product_code' => '6205_S', 'featured_image' => null]);
    $heatResistant = Product::factory()->create(['product_code' => '6205-2Z/VA201_S', 'featured_image' => null]);

    $path = writeImageFixture([
        ['6205-2Z/VA201_S', 'SKF egysorú mélyhornyú hőálló golyóscsapágy', 'https://cdn.test/hot.png'],
        ['6000, 6200', 'SKF egysorú mélyhornyú golyóscsapágy', 'https://cdn.test/deep-groove.png'],
    ]);

    (new ProductImageImporter())->import($path);

    expect($plain->fresh()->featured_image)->toBe('https://cdn.test/deep-groove.png')
        ->and($heatResistant->fresh()->featured_image)->toBe('https://cdn.test/hot.png');
});

it('keeps the per-code cap for code lists that are not SKF rows', function (): void {
    Product::factory()->count(3)->sequence(fn ($sequence): array => ['product_code' => 'AB ' . $sequence->index])->create();

    $path = writeImageFixture([
        ['AB, XY', 'Valami más', 'https://cdn.test/ab.png'],
    ]);

    $stats = (new ProductImageImporter())->import($path, maxVariantsPerCode: 1);

    expect($stats['skipped'])->toBe(1)
        ->and(Product::query()->whereNotNull('featured_image')->where('featured_image', 'https://cdn.test/ab.png')->count())->toBe(0);
});

it('treats a comma after a wildcard as part of a single code, not a list', function (): void {
    $product = Product::factory()->create(['product_code' => 'W2 csavar', 'featured_image' => null]);

    $path = writeImageFixture([
        ['TORRO ... W1, W2', 'TORRO bilincs', 'https://cdn.test/torro.png'],
    ]);

    (new ProductImageImporter())->import($path);

    expect($product->fresh()->featured_image)->toBeNull();
});

it('never lets a series image replace a picture uploaded in the admin', function (): void {
    $uploaded = Product::factory()->create(['product_code' => '6205_S', 'featured_image' => 'products/featured/6205.jpg']);
    $imported = Product::factory()->create(['product_code' => '6206_S', 'featured_image' => 'https://cdn.test/old.png']);

    $path = writeImageFixture([
        ['6000, 6200', 'SKF egysorú mélyhornyú golyóscsapágy', 'https://cdn.test/deep-groove.png'],
    ]);

    (new ProductImageImporter())->import($path);

    expect($uploaded->fresh()->featured_image)->toBe('products/featured/6205.jpg')
        ->and($imported->fresh()->featured_image)->toBe('https://cdn.test/deep-groove.png');
});
