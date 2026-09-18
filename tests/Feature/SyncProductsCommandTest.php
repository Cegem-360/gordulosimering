<?php

declare(strict_types=1);

use App\Models\Product;

use function Pest\Laravel\artisan;

/**
 * @param  array<int, array<int, string>>  $rows
 */
function writeCommandFixture(array $rows): string
{
    $lines = [implode("\t", array_fill(0, 40, 'col'))];

    foreach ($rows as $row) {
        $lines[] = implode("\t", array_pad($row, 40, ''));
    }

    $path = tempnam(sys_get_temp_dir(), 'sync_cmd_') . '.tsv';
    file_put_contents($path, implode("\n", $lines) . "\n");

    return $path;
}

/**
 * @return array<int, string>
 */
function commandRow(string $code, string $name, string $visible = 'IGEN'): array
{
    $row = array_fill(0, 40, '');
    $row[1] = $visible;
    $row[2] = $code;
    $row[4] = $name;
    $row[39] = 'Nem';

    return $row;
}

it('fails when the given TSV does not exist', function (): void {
    artisan('app:sync-products', ['--path' => '/nem/letezik.tsv'])
        ->assertFailed();
});

it('syncs the products from the given TSV', function (): void {
    Product::query()->create(['product_code' => 'A', 'slug' => 'a', 'name' => 'Régi']);

    $path = writeCommandFixture([
        commandRow('A', 'Új név'),
        commandRow('B', 'Új termék'),
    ]);

    artisan('app:sync-products', ['--path' => $path])
        ->assertSuccessful();

    expect(Product::query()->where('product_code', 'A')->value('name'))->toBe('Új név')
        ->and(Product::query()->where('product_code', 'B')->exists())->toBeTrue();
});

it('writes nothing when --dry-run is passed', function (): void {
    Product::query()->create(['product_code' => 'A', 'slug' => 'a', 'name' => 'Régi']);

    $path = writeCommandFixture([commandRow('A', 'Új név')]);

    artisan('app:sync-products', ['--path' => $path, '--dry-run' => true])
        ->assertSuccessful();

    expect(Product::query()->where('product_code', 'A')->value('name'))->toBe('Régi');
});

it('skips products that are not web visible when --only-web-visible is passed', function (): void {
    $path = writeCommandFixture([
        commandRow('LATHATO', 'Látható'),
        commandRow('REJTETT', 'Rejtett', 'NEM'),
    ]);

    artisan('app:sync-products', ['--path' => $path, '--only-web-visible' => true])
        ->assertSuccessful();

    expect(Product::query()->where('product_code', 'LATHATO')->exists())->toBeTrue()
        ->and(Product::query()->where('product_code', 'REJTETT')->exists())->toBeFalse();
});
