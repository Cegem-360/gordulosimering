<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\CategoryImporter;
use Tests\TestCase;

it('does not list American Express among the accepted cards', function (): void {
    /** @var TestCase $this */
    $this->get(route('documents'))->assertOk()
        ->assertSee('MasterCard')
        ->assertDontSee('American Express');
});

it('offers personal pickup as a free shipping method', function (): void {
    $pickup = ShippingMethod::query()->where('name', 'pickup')->first();

    expect($pickup)->not->toBeNull()
        ->and($pickup->title)->toBe('Személyes átvétel')
        ->and($pickup->cost)->toBe(0);
});

it('renames the bearing housings root category and leaves other categories alone', function (): void {
    $housings = Category::query()->create(['name' => 'CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)', 'slug' => 'csapagyhazak-s1s2s3s4s5']);
    $child = Category::query()->create(['name' => 'CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)', 'slug' => 'nested', 'category_id' => $housings->id]);

    $migration = require database_path('migrations/2026_09_23_143238_rename_bearing_housings_category.php');
    $migration->up();

    expect($housings->fresh())->name->toBe('CSAPÁGYHÁZAK')->slug->toBe('csapagyhazak')
        ->and($child->fresh()->name)->toBe('CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)');

    $migration->down();

    expect($housings->fresh())->name->toBe('CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)')->slug->toBe('csapagyhazak-s1s2s3s4s5');
});

it('turns the single-product categories into products of their parent category', function (): void {
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $railway = Category::query()->create(['name' => 'SKF VASÚTI ÁGYTOKCSAPÁGY', 'slug' => 'skf-vasuti-agytokcsapagy', 'category_id' => $bearings->id]);
    $product = Product::factory()->create(['name' => 'SKF VASÚTI ÁGYTOKCSAPÁGY']);
    $railway->products()->attach($product);
    $bearings->products()->attach($linkedTwice = Product::factory()->create());
    $railway->products()->attach($linkedTwice);
    $elsewhere = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $sameNameElsewhere = Category::query()->create(['name' => 'SKF VASÚTI ÁGYTOKCSAPÁGY', 'slug' => 'other', 'category_id' => $elsewhere->id]);

    $migration = require database_path('migrations/2026_09_25_102332_turn_single_product_categories_into_products.php');
    $migration->up();

    expect($railway->fresh())->toBeNull()
        ->and($bearings->products()->pluck('products.id')->sort()->values()->all())
        ->toBe(collect([$product->id, $linkedTwice->id])->sort()->values()->all())
        ->and($sameNameElsewhere->fresh())->not->toBeNull();
});

it('imports the single-product rows of the category sheet as products, not categories', function (): void {
    $importer = new CategoryImporter();
    $importer->importTree(database_path('data/web_kategoriak.tsv'));
    $product = Product::factory()->create(['name' => 'SKF VASÚTI ÁGYTOKCSAPÁGY']);
    $importer->linkProducts();

    foreach (['SKF VASÚTI ÁGYTOKCSAPÁGY', 'SKF/LINCOLN 084110', 'GRAFITOS ZSÍR', 'SEEGER DIN9926'] as $name) {
        expect(Category::query()->where('name', $name)->exists())->toBeFalse();
    }

    expect($product->categories()->pluck('name')->all())->toBe(['CSAPÁGYAK']);
});

it('does not show the brand logo strip on the homepage', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()->assertDontSeeHtml('>Márkáink</h2>');
});
