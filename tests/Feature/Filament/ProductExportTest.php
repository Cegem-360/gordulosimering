<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductCsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;

/**
 * @return list<array<string, string>>
 */
function parseProductCsv(string $content): array
{
    expect($content)->toStartWith("\xEF\xBB\xBF");

    $lines = array_map(
        fn (string $line): array => str_getcsv($line, ';', escape: ''),
        array_filter(explode("\n", mb_substr($content, 1))),
    );
    $header = array_shift($lines);

    return array_map(fn (array $row): array => array_combine($header, $row), $lines);
}

function exportProductsToString(?Builder $query = null): string
{
    $handle = fopen('php://memory', 'w+b');
    resolve(ProductCsvExporter::class)->write($handle, $query ?? Product::query());
    rewind($handle);

    return stream_get_contents($handle);
}

it('exports every product column with Hungarian labels and the category names', function (): void {
    $product = Product::factory()->create([
        'product_code' => 'TESZT-6204',
        'name' => 'Csapágy 6204 "2RS"',
        'images' => ['products/images/a.jpg'],
    ]);
    $product->categories()->attach([
        Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak'])->getKey(),
        Category::query()->create(['name' => 'Golyóscsapágyak', 'slug' => 'golyoscsapagyak'])->getKey(),
    ]);

    $rows = parseProductCsv(exportProductsToString());

    expect($rows)->toHaveCount(1)
        ->and($rows[0])->toHaveKeys(['id', 'Termékkód (product_code)', 'Név (name)', 'Kategóriák (categories)', 'Márka (brand)', 'Anyag (material)', 'Belső átmérő (d) (inner_diameter)'])
        ->and($rows[0]['Termékkód (product_code)'])->toBe('TESZT-6204')
        ->and($rows[0]['Név (name)'])->toBe('Csapágy 6204 "2RS"')
        ->and($rows[0]['További képek (images)'])->toBe('["products\/images\/a.jpg"]')
        ->and($rows[0]['Kategóriák (categories)'])->toBe('Csapágyak, Golyóscsapágyak');
});

it('writes only the header for an empty selection', function (): void {
    Product::factory()->create();

    $rows = parseProductCsv(exportProductsToString(Product::query()->whereKey([])));

    expect($rows)->toBe([]);
});

it('exports the products to a file from the command', function (): void {
    Product::factory()->create(['product_code' => 'LATHATO']);
    Product::factory()->hidden()->create(['product_code' => 'REJTETT']);
    $path = storage_path('framework/testing/termekek-export.csv');

    artisan('app:export-products', ['--path' => $path])
        ->expectsOutputToContain('2 termék exportálva')
        ->assertSuccessful();

    expect(array_column(parseProductCsv(File::get($path)), 'Termékkód (product_code)'))
        ->toEqualCanonicalizing(['LATHATO', 'REJTETT']);

    artisan('app:export-products', ['--path' => $path, '--only-web-visible' => true])
        ->expectsOutputToContain('1 termék exportálva')
        ->assertSuccessful();

    expect(array_column(parseProductCsv(File::get($path)), 'Termékkód (product_code)'))->toBe(['LATHATO']);

    File::delete($path);
});

it('downloads all products and the selected products from the admin list', function (): void {
    actingAs(User::factory()->admin()->create());
    $products = Product::factory()->count(3)->create();

    Livewire::test(ListProducts::class)
        ->callAction('export')
        ->assertFileDownloaded('termekek-' . now()->format('Y-m-d') . '.csv');

    Livewire::test(ListProducts::class)
        ->callTableBulkAction('export', $products->take(2))
        ->assertFileDownloaded('termekek-kijeloltek-' . now()->format('Y-m-d') . '.csv');
});
