<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\StockSyncer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config(['database.connections.integra7' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('integra7');

    Schema::connection('integra7')->create('quantities', function (Blueprint $table): void {
        $table->id();
        $table->string('product_code');
        $table->string('store_code');
        $table->decimal('quantity', 10, 4);
        $table->decimal('reserved', 10, 4);
    });
});

/**
 * @param  array<int, array{0: string, 1: string, 2: float, 3: float}>  $rows
 */
function seedIntegraQuantities(array $rows): void
{
    DB::connection('integra7')->table('quantities')->insert(array_map(fn (array $row): array => [
        'product_code' => $row[0],
        'store_code' => $row[1],
        'quantity' => $row[2],
        'reserved' => $row[3],
    ], $rows));
}

it('sums the free stock of every store onto the product', function (): void {
    $product = Product::factory()->create(['product_code' => '6204-2RS', 'stock_quantity' => 0]);

    seedIntegraQuantities([
        ['6204-2RS', 'GORSIM01', 10, 2],
        ['6204-2RS', 'GORSIM02', 5, 0],
    ]);

    $this->artisan('app:sync-stock')->assertSuccessful();

    expect($product->fresh()->stock_quantity)->toBe(13.0)
        ->and($product->fresh()->isInStock())->toBeTrue();
});

it('zeroes the stock of products missing from Integra7 or oversold there', function (): void {
    $missing = Product::factory()->create(['product_code' => 'GONE', 'stock_quantity' => 8]);
    $oversold = Product::factory()->create(['product_code' => 'OVER', 'stock_quantity' => 3]);

    seedIntegraQuantities([['OVER', 'GORSIM01', 1, 4]]);

    $stats = resolve(StockSyncer::class)->sync();

    expect($missing->fresh()->stock_quantity)->toBe(0.0)
        ->and($oversold->fresh()->stock_quantity)->toBe(0.0)
        ->and($stats)->toBe(['updated' => 2, 'unchanged' => 0, 'missing' => 1]);
});

it('leaves unchanged stock alone and writes nothing on a dry run', function (): void {
    $same = Product::factory()->create(['product_code' => 'SAME', 'stock_quantity' => 4]);
    $changed = Product::factory()->create(['product_code' => 'NEW', 'stock_quantity' => 0]);

    seedIntegraQuantities([
        ['SAME', 'GORSIM01', 4, 0],
        ['NEW', 'GORSIM01', 9, 0],
    ]);

    $stats = resolve(StockSyncer::class)->sync(dryRun: true);

    expect($stats)->toBe(['updated' => 1, 'unchanged' => 1, 'missing' => 0])
        ->and($changed->fresh()->stock_quantity)->toBe(0.0)
        ->and($same->fresh()->stock_quantity)->toBe(4.0);
});

it('matches product codes that the ERP pads with spaces', function (): void {
    $product = Product::factory()->create(['product_code' => 'PAD-1', 'stock_quantity' => 0]);

    seedIntegraQuantities([['PAD-1   ', 'GORSIM01', 6, 0]]);

    resolve(StockSyncer::class)->sync();

    expect($product->fresh()->stock_quantity)->toBe(6.0);
});
