<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\DiscountGroup;
use App\Models\IntegraKnownProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\Integra7Syncer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

beforeEach(function (): void {
    config(['database.connections.integra7' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('integra7');

    Schema::connection('integra7')->create('products', function (Blueprint $table): void {
        $table->id();
        $table->string('code');
        $table->string('name');
        $table->string('size')->nullable();
        $table->boolean('is_discount')->default(false);
        $table->decimal('discountpercent', 5, 2)->default(0);
        $table->decimal('nettoprice', 16, 6);
        $table->decimal('taxpercent', 5, 2)->default(27);
        $table->boolean('inactive')->default(false);
        $table->string('quantity_unit')->nullable();
        $table->string('product_group_code')->nullable();
        $table->string('product_type_code')->nullable();
    });

    Schema::connection('integra7')->create('product_groups', function (Blueprint $table): void {
        $table->id();
        $table->string('code');
        $table->string('name');
    });

    Schema::connection('integra7')->create('product_types', function (Blueprint $table): void {
        $table->id();
        $table->string('code');
        $table->string('name');
    });

    Schema::connection('integra7')->create('quantities', function (Blueprint $table): void {
        $table->id();
        $table->string('product_code');
        $table->string('store_code');
        $table->decimal('quantity', 10, 4);
        $table->decimal('reserved', 10, 4);
    });

    Schema::connection('integra7')->create('parameters', function (Blueprint $table): void {
        $table->id();
        $table->boolean('upload_progress')->default(false);
    });
    DB::connection('integra7')->table('parameters')->insert(['upload_progress' => false]);
});

/**
 * @param  array<string, mixed>  $attributes
 */
function seedIntegraProduct(string $code, array $attributes = []): void
{
    DB::connection('integra7')->table('products')->insert([
        'code' => $code,
        'name' => 'Csapágy ' . $code,
        'size' => '20X47X14',
        'nettoprice' => 1000,
        'quantity_unit' => 'db        ',
        ...$attributes,
    ]);
}

function seedIntegraGroup(string $code, string $name): void
{
    DB::connection('integra7')->table('product_groups')->insert(['code' => $code, 'name' => $name]);
}

function seedIntegraType(string $code, string $name): void
{
    DB::connection('integra7')->table('product_types')->insert(['code' => $code, 'name' => $name]);
}

function seedIntegraQuantity(string $code, string $store, float $quantity, float $reserved = 0): void
{
    DB::connection('integra7')->table('quantities')->insert([
        'product_code' => $code,
        'store_code' => $store,
        'quantity' => $quantity,
        'reserved' => $reserved,
    ]);
}

it('sums the free stock of every store onto the product', function (): void {
    /** @var TestCase $this */
    $product = Product::factory()->create(['product_code' => '6204-2RS', 'stock_quantity' => 0]);
    seedIntegraProduct('6204-2RS');
    seedIntegraQuantity('6204-2RS', 'GORSIM01', 10, 2);
    seedIntegraQuantity('6204-2RS', 'GORSIM02', 5);

    $this->artisan('app:sync-integra7')->assertSuccessful();

    expect($product->fresh()->stock_quantity)->toBe(13.0)
        ->and($product->fresh()->isInStock())->toBeTrue();
});

it('takes the price, sale, name, size, unit and inactive flag from Integra7', function (): void {
    $product = Product::factory()->create([
        'product_code' => '6204',
        'name' => 'Régi név',
        'net_selling_price' => 500,
        'gross_selling_price' => 635,
        'is_on_sale' => false,
        'sale_percentage' => 0,
        'is_inactive' => false,
    ]);
    seedIntegraProduct('6204', [
        'name' => 'SKF 6204 golyóscsapágy ',
        'size' => '20X47X14',
        'nettoprice' => 1234.5,
        'taxpercent' => 27,
        'is_discount' => true,
        'discountpercent' => 15,
        'inactive' => true,
    ]);

    resolve(Integra7Syncer::class)->sync();

    expect($product->fresh())
        ->name->toBe('SKF 6204 golyóscsapágy')
        ->size->toBe('20X47X14')
        ->quantity_unit->toBe('db')
        ->net_selling_price->toBe('1234.50')
        ->gross_selling_price->toBe('1567.82')
        ->is_on_sale->toBeTrue()
        ->sale_percentage->toBe('15.00')
        ->is_inactive->toBeTrue();
});

it('keeps the webshop data of the product untouched', function (): void {
    $product = Product::factory()->create([
        'product_code' => 'KEEP',
        'slug' => 'sajat-slug',
        'is_web_visible' => true,
        'description' => 'Webshopos leírás',
    ]);
    seedIntegraProduct('KEEP');

    resolve(Integra7Syncer::class)->sync();

    expect($product->fresh())
        ->slug->toBe('sajat-slug')
        ->is_web_visible->toBeTrue()
        ->description->toBe('Webshopos leírás');
});

it('zeroes the stock of products missing from Integra7 or oversold there, but keeps their data', function (): void {
    $missing = Product::factory()->create(['product_code' => 'GONE', 'stock_quantity' => 8, 'net_selling_price' => 700]);
    $oversold = Product::factory()->create(['product_code' => 'OVER', 'stock_quantity' => 3]);
    seedIntegraProduct('OVER');
    seedIntegraQuantity('OVER', 'GORSIM01', 1, 4);

    $stats = resolve(Integra7Syncer::class)->sync();

    expect($missing->fresh())
        ->stock_quantity->toBe(0.0)
        ->net_selling_price->toBe('700.00')
        ->and($oversold->fresh()->stock_quantity)->toBe(0.0)
        ->and($stats)->toMatchArray(['updated' => 2, 'unchanged' => 0, 'missing' => 1]);
});

it('only remembers the Integra7 codes on the first run, without creating products', function (): void {
    Product::factory()->create(['product_code' => 'IN-SHOP']);
    seedIntegraProduct('IN-SHOP');
    seedIntegraProduct('LEFT-OUT  ');

    $stats = resolve(Integra7Syncer::class)->sync();

    expect($stats['created'])->toBe(0)
        ->and(Product::query()->where('product_code', 'LEFT-OUT')->exists())->toBeFalse()
        ->and(IntegraKnownProduct::query()->pluck('code')->sort()->values()->all())->toBe(['IN-SHOP', 'LEFT-OUT']);
});

it('creates a hidden product from a code that is new in Integra7', function (): void {
    seedIntegraProduct('OLD');
    resolve(Integra7Syncer::class)->sync();

    seedIntegraType('110', 'csapágy SKF');
    seedIntegraProduct('UJ 6205 ', ['name' => 'SKF 6205 golyóscsapágy', 'size' => '25X52X15', 'nettoprice' => 2000, 'product_group_code' => 'S5', 'product_type_code' => '110']);
    seedIntegraQuantity('UJ 6205 ', 'GORSIM01', 7, 2);

    $stats = resolve(Integra7Syncer::class)->sync();

    expect($stats['created'])->toBe(1)
        ->and(Product::query()->where('product_code', 'UJ 6205')->sole())
        ->name->toBe('SKF 6205 golyóscsapágy')
        ->slug->toBe('uj-6205')
        ->net_selling_price->toBe('2000.00')
        ->gross_selling_price->toBe('2540.00')
        ->group_code->toBe('S5')
        ->product_variety->toBe('csapágy SKF')
        ->stock_quantity->toBe(5.0)
        ->inner_diameter->toBe(25.0)
        ->is_web_visible->toBeFalse()
        ->and(Product::query()->where('product_code', 'OLD')->exists())->toBeFalse();

    expect(resolve(Integra7Syncer::class)->sync()['created'])->toBe(0)
        ->and(Product::query()->where('product_code', 'UJ 6205')->count())->toBe(1);
});

it('skips new inactive codes and new codes already in the webshop, and gives clashing slugs a suffix', function (): void {
    seedIntegraProduct('OLD');
    resolve(Integra7Syncer::class)->sync();

    Product::factory()->create(['product_code' => 'MANUAL', 'slug' => 'manual']);
    Product::factory()->create(['product_code' => 'OTHER', 'slug' => 'clash']);
    seedIntegraProduct('MANUAL');
    seedIntegraProduct('KIVEZETETT', ['inactive' => true]);
    seedIntegraProduct('CLASH');

    $stats = resolve(Integra7Syncer::class)->sync();

    expect($stats['created'])->toBe(1)
        ->and(Product::query()->where('product_code', 'MANUAL')->count())->toBe(1)
        ->and(Product::query()->where('product_code', 'KIVEZETETT')->exists())->toBeFalse()
        ->and(Product::query()->where('product_code', 'CLASH')->value('slug'))->toBe('clash-1')
        ->and(IntegraKnownProduct::query()->where('code', 'KIVEZETETT')->exists())->toBeTrue();
});

it('counts the new products on a dry run without creating or remembering them', function (): void {
    seedIntegraProduct('OLD');
    resolve(Integra7Syncer::class)->sync();
    seedIntegraProduct('UJ');

    $stats = resolve(Integra7Syncer::class)->sync(dryRun: true);

    expect($stats['created'])->toBe(1)
        ->and(Product::query()->where('product_code', 'UJ')->exists())->toBeFalse()
        ->and(IntegraKnownProduct::query()->where('code', 'UJ')->exists())->toBeFalse();
});

it('skips the whole sync while the ERP is uploading to Integra7', function (): void {
    /** @var TestCase $this */
    $product = Product::factory()->create(['product_code' => 'HALF', 'stock_quantity' => 8]);
    DB::connection('integra7')->table('parameters')->update(['upload_progress' => true]);

    $this->artisan('app:sync-integra7')
        ->expectsOutputToContain('a szinkron kimarad')
        ->assertSuccessful();

    expect($product->fresh()->stock_quantity)->toBe(8.0)
        ->and(IntegraKnownProduct::query()->count())->toBe(0);
});

it('counts products already in sync as unchanged and writes nothing on a dry run', function (): void {
    Product::factory()->create(['product_code' => 'SAME']);
    $changed = Product::factory()->create(['product_code' => 'NEW', 'stock_quantity' => 0]);
    seedIntegraProduct('SAME');
    seedIntegraProduct('NEW');
    seedIntegraQuantity('NEW', 'GORSIM01', 9);

    resolve(Integra7Syncer::class)->sync();
    seedIntegraQuantity('NEW', 'GORSIM02', 1);

    $stats = resolve(Integra7Syncer::class)->sync(dryRun: true);

    expect($stats)->toMatchArray(['updated' => 1, 'unchanged' => 1, 'missing' => 0])
        ->and($changed->fresh()->stock_quantity)->toBe(9.0);
});

it('matches product codes that the ERP pads with spaces', function (): void {
    $product = Product::factory()->create(['product_code' => 'PAD-1', 'stock_quantity' => 0]);
    seedIntegraProduct('PAD-1   ');
    seedIntegraQuantity('PAD-1   ', 'GORSIM01', 6);

    resolve(Integra7Syncer::class)->sync();

    expect($product->fresh()->stock_quantity)->toBe(6.0);
});

it('takes the product group and product type from Integra7', function (): void {
    $product = Product::factory()->create(['product_code' => '6204', 'group_code' => 'CD', 'product_variety' => 'csapágy (olcsóbb, keleti)']);
    seedIntegraType('110', 'csapágy SKF   ');
    seedIntegraProduct('6204', ['product_group_code' => 'S5  ', 'product_type_code' => '110']);

    resolve(Integra7Syncer::class)->sync();

    expect($product->fresh())
        ->group_code->toBe('S5')
        ->product_variety->toBe('csapágy SKF');
});

it('keeps the product group and type when Integra7 has none for the product', function (): void {
    $product = Product::factory()->create(['product_code' => 'NOGROUP', 'group_code' => 'CT', 'product_variety' => 'szimering']);
    seedIntegraProduct('NOGROUP', ['product_group_code' => '  ', 'product_type_code' => '404']);

    resolve(Integra7Syncer::class)->sync();

    expect($product->fresh())
        ->group_code->toBe('CT')
        ->product_variety->toBe('szimering');
});

it('names the existing product groups and adds the new ones from Integra7', function (): void {
    DiscountGroup::factory()->create(['code' => 'CT', 'name' => null]);
    seedIntegraGroup('CT  ', 'Tőkés (minőségi) csapágy ');
    seedIntegraGroup('SM', 'Simmering');

    resolve(Integra7Syncer::class)->sync();

    expect(DiscountGroup::query()->orderBy('code')->pluck('name', 'code')->all())
        ->toBe(['CT' => 'Tőkés (minőségi) csapágy', 'SM' => 'Simmering']);
});

it('leaves the product groups alone on a dry run', function (): void {
    seedIntegraGroup('SM', 'Simmering');

    resolve(Integra7Syncer::class)->sync(dryRun: true);

    expect(DiscountGroup::query()->count())->toBe(0);
});

it('lets the admin list only the hidden products', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->admin()->create());
    $visible = Product::factory()->create();
    $hidden = Product::factory()->hidden()->create();

    Livewire::test(ListProducts::class)
        ->filterTable('is_web_visible', false)
        ->assertCanSeeTableRecords([$hidden])
        ->assertCanNotSeeTableRecords([$visible]);
});
