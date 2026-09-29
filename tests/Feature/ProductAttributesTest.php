<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\Integra7Syncer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('fills the attributes when a product is created', function (): void {
    $product = Product::factory()->create(['name' => 'Gumiházas simmering, VITON', 'size' => '25X47X8']);

    expect($product->fresh())
        ->inner_diameter->toBe(25.0)
        ->outer_diameter->toBe(47.0)
        ->width->toBe(8.0)
        ->brand->toBeNull()
        ->material->toBe('FKM (Viton)');
});

it('recalculates the attributes when the name or size changes', function (): void {
    $product = Product::factory()->create(['name' => 'KOYO golyóscsapágy', 'size' => '20X47X14']);

    $product->update(['name' => 'SKF golyóscsapágy', 'size' => '30X62X16']);

    expect($product->fresh())
        ->brand->toBe('SKF')
        ->inner_diameter->toBe(30.0)
        ->width->toBe(16.0);
});

it('saves a product without name or size', function (): void {
    $product = Product::factory()->create(['name' => null, 'size' => null]);

    expect($product->fresh())
        ->inner_diameter->toBeNull()
        ->brand->toBeNull()
        ->material->toBeNull();
});

it('recalculates the attributes when the Integra7 sync changes the name or size', function (): void {
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

    $product = Product::factory()->create(['product_code' => '6204', 'name' => 'KOYO golyóscsapágy', 'size' => '20X47X14']);
    DB::connection('integra7')->table('products')->insert([
        'code' => '6204', 'name' => 'SKF golyóscsapágy', 'size' => '25X52X15', 'nettoprice' => 1000,
    ]);

    resolve(Integra7Syncer::class)->sync();

    expect($product->fresh())
        ->brand->toBe('SKF')
        ->inner_diameter->toBe(25.0)
        ->outer_diameter->toBe(52.0);
});

it('backfills the attributes of existing products and leaves up-to-date ones alone', function (): void {
    $stale = Product::factory()->create(['name' => 'SKF golyóscsapágy', 'size' => '25X47X8']);
    Product::factory()->create(['name' => 'KOYO golyóscsapágy', 'size' => '30X62X16']);
    DB::table('products')->where('id', $stale->id)->update(['brand' => null, 'inner_diameter' => null]);

    $this->artisan('app:extract-product-attributes')
        ->expectsOutput('Frissítve: 1 termék.')
        ->assertSuccessful();

    expect($stale->fresh())->brand->toBe('SKF')->inner_diameter->toBe(25.0);

    $this->artisan('app:extract-product-attributes')
        ->expectsOutput('Frissítve: 0 termék.')
        ->assertSuccessful();
});
