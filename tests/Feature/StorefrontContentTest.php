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

it('renames the hand tools root and the instruments categories', function (): void {
    $tools = Category::query()->create(['name' => 'KÉZI SZERSZÁMOK, MŰSZEREK', 'slug' => 'kezi-szerszamok-muszerek']);
    $instruments = Category::query()->create(['name' => 'MŰSZEREK', 'slug' => 'kezi-szerszamok-muszerek-muszerek', 'category_id' => $tools->id]);
    $measuring = Category::query()->create(['name' => 'MÉRŐESZKÖZÖK, MŰSZEREK', 'slug' => 'meroeszkozok', 'category_id' => $tools->id]);
    $elsewhere = Category::query()->create(['name' => 'MŰSZEREK', 'slug' => 'other-muszerek']);
    $beltDrive = Category::query()->create(['name' => 'SZÍJHATÁS', 'slug' => 'szijhatas']);
    $beltInstrument = Category::query()->create(['name' => 'MÉRŐ ÉS ELLENÖRZŐ MŰSZER', 'slug' => 'belt-instrument', 'category_id' => $beltDrive->id]);

    $migration = require database_path('migrations/2026_09_25_103310_rename_hand_tools_and_instruments_categories.php');
    $migration->up();

    expect($tools->fresh())->name->toBe('KÉZISZERSZÁMOK ÉS MŰSZEREK')->slug->toBe('keziszerszamok-es-muszerek')
        ->and($instruments->fresh())->name->toBe('MÉRŐ- ÉS ELLENŐRZŐ MŰSZEREK')
        ->slug->toBe('keziszerszamok-es-muszerek-mero-es-ellenorzo-muszerek')
        ->and($measuring->fresh()->name)->toBe('MÉRŐESZKÖZÖK, MŰSZEREK')
        ->and($elsewhere->fresh()->name)->toBe('MŰSZEREK')
        ->and($beltInstrument->fresh())->name->toBe('MÉRŐ- ÉS ELLENŐRZŐ MŰSZER')->slug->toBe('belt-instrument');

    $migration->down();

    expect($tools->fresh())->name->toBe('KÉZI SZERSZÁMOK, MŰSZEREK')->slug->toBe('kezi-szerszamok-muszerek')
        ->and($instruments->fresh()->name)->toBe('MŰSZEREK')
        ->and($beltInstrument->fresh()->name)->toBe('MÉRŐ ÉS ELLENÖRZŐ MŰSZER');
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

it('turns the SEEGER DIN categories into products of their parent category', function (): void {
    $shaft = Category::query()->create(['name' => 'TENGELYRE', 'slug' => 'tengelyre']);
    $bore = Category::query()->create(['name' => 'FURATBA', 'slug' => 'furatba']);
    $din6799 = Category::query()->create(['name' => 'SEEGER DIN6799', 'slug' => 'seeger-din6799', 'category_id' => $shaft->id]);
    $din9928 = Category::query()->create(['name' => 'SEEGER DIN9928', 'slug' => 'seeger-din9928', 'category_id' => $bore->id]);
    $din6799->products()->attach($ring = Product::factory()->create(['name' => 'SEEGER DIN6799']));
    $din9928->products()->attach($boreRing = Product::factory()->create(['name' => 'SEEGER DIN9928']));

    $migration = require database_path('migrations/2026_09_25_102714_turn_seeger_din_categories_into_products.php');
    $migration->up();

    expect($din6799->fresh())->toBeNull()
        ->and($din9928->fresh())->toBeNull()
        ->and($shaft->products()->pluck('products.id')->all())->toBe([$ring->id])
        ->and($bore->products()->pluck('products.id')->all())->toBe([$boreRing->id]);
});

it('imports the single-product rows of the category sheet as products, not categories', function (): void {
    $importer = new CategoryImporter();
    $importer->importTree(database_path('data/web_kategoriak.tsv'));
    $product = Product::factory()->create(['name' => 'SKF VASÚTI ÁGYTOKCSAPÁGY']);
    $importer->linkProducts();

    foreach (['SKF VASÚTI ÁGYTOKCSAPÁGY', 'SKF/LINCOLN 084110', 'GRAFITOS ZSÍR', 'SEEGER DIN9926', 'SEEGER DIN6799', 'SEEGER DIN9927', 'SEEGER DIN9928'] as $name) {
        expect(Category::query()->where('name', $name)->exists())->toBeFalse();
    }

    expect($product->categories()->pluck('name')->all())->toBe(['CSAPÁGYAK']);
});

it('does not show the brand logo strip on the homepage', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()->assertDontSeeHtml('>Márkáink</h2>');
});

it('splits the work safety category into shoes and gloves', function (): void {
    $shoe = Product::factory()->create(['name' => 'BETA mérsékelten vízálló bőr munkavédelmi cipő']);
    $lace = Product::factory()->create(['name' => 'BETA cipőfűző narancs/fekete 120cm']);
    $glove = Product::factory()->create(['name' => 'Védőkesztyű PU tenyérmártott prec. rug. FEKETE 9']);

    $importer = new CategoryImporter();
    $importer->importTree(database_path('data/web_kategoriak.tsv'));
    $importer->linkProducts();

    $safety = Category::query()->whereNull('category_id')->where('name', 'MUNKAVÉDELMI CIPŐ, KESZTYŰ')->sole();

    expect($safety->children()->pluck('name')->all())->toBe(['MUNKAVÉDELMI CIPŐ', 'MUNKAVÉDELMI KESZTYŰ'])
        ->and($shoe->categories()->pluck('name')->all())->toBe(['MUNKAVÉDELMI CIPŐ'])
        ->and($lace->categories()->pluck('name')->all())->toBe(['MUNKAVÉDELMI CIPŐ'])
        ->and($glove->categories()->pluck('name')->all())->toBe(['MUNKAVÉDELMI KESZTYŰ']);
});

it('puts the chemicals subcategories into the order the client asked for', function (): void {
    $chemicals = Category::query()->create(['name' => 'VEGYI ÁRUK', 'slug' => 'vegyi-aruk']);
    $before = ['EGYÉB RAGASZTÓ ÉS TÖMÍTŐ', 'PILLANATRAGASZTÓ', 'UV FÉNYRE KÖTŐ RAGASZTÓ', 'CSAVARRÖGZÍTŐ', 'ADAGOLÓ ESZKÖZ'];
    foreach ($before as $index => $name) {
        Category::query()->create(['name' => $name, 'slug' => 'c' . $index, 'category_id' => $chemicals->id, 'sort_order' => 100 + $index * 10]);
    }

    $migration = require database_path('migrations/2026_09_25_114159_reorder_chemicals_subcategories.php');
    $migration->up();

    expect($chemicals->children()->pluck('name')->all())
        ->toBe(['PILLANATRAGASZTÓ', 'CSAVARRÖGZÍTŐ', 'UV FÉNYRE KÖTŐ RAGASZTÓ', 'EGYÉB RAGASZTÓ ÉS TÖMÍTŐ', 'ADAGOLÓ ESZKÖZ'])
        ->and($chemicals->children()->pluck('sort_order')->all())->toBe([100, 110, 120, 130, 140]);
});

it('lists the chemicals subcategories in the client\'s order after a fresh import', function (): void {
    (new CategoryImporter())->importTree(database_path('data/web_kategoriak.tsv'));

    $chemicals = Category::query()->whereNull('category_id')->where('name', 'VEGYI ÁRUK')->sole();

    expect($chemicals->children()->pluck('name')->all())->toBe([
        'PILLANATRAGASZTÓ', 'CSAVARRÖGZÍTŐ', 'CSAPÁGYRÖGZÍTŐ', 'MENETTÖMÍTŐ', 'FELÜLETTÖMÍTŐ',
        'KÉTKOMPONENSŰ RAGASZTÓ', 'BERÁGÓDÁSGÁTLÓ', 'TISZTÍTÁS, ZSÍRTALANÍTÁS', 'AKTIVÁTOR, PRIMER', 'ZSÍR, OLAJ',
        'UV FÉNYRE KÖTŐ RAGASZTÓ', 'EGYÉB RAGASZTÓ ÉS TÖMÍTŐ', 'RAGASZTÓSZALAG', 'KARBANTARTÁSI TERMÉKEK', 'ADAGOLÓ ESZKÖZ',
    ]);
});

it('keeps the header on screen and offers a back-to-top button', function (): void {
    /** @var TestCase $this */
    $this->get(route('contact'))->assertOk()
        ->assertSeeHtml('class="sticky top-0 z-40 bg-white border-b"')
        ->assertSeeHtml('aria-label="Vissza az oldal tetejére"')
        ->assertSeeHtml("window.scrollTo({ top: 0, behavior: 'smooth' })");
});
