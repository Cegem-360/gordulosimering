<?php

declare(strict_types=1);

use App\Filament\Resources\Catalogs\Pages\CreateCatalog;
use App\Filament\Resources\Catalogs\Pages\ListCatalogs;
use App\Livewire\Products\Categories\Index as CategoriesIndex;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('links the new homepage boxes to Márkáink, Katalógusok and the delivery section', function (): void {
    get(route('index'))->assertOk()->assertSee(['Forgalmazott márkáink', 'Katalógusok', 'Szállítási költségek'])->assertSeeHtml('href="' . route('brands') . '"')->assertSeeHtml('href="' . route('catalogs') . '"')->assertSeeHtml('href="' . route('services') . '#hazhozszallitas"');
});

it('links each brand with products to its products, by brand filter or the brand listing', function (): void {
    Product::factory()->count(2)->create()->each(fn (Product $product) => $product->forceFill(['brand' => 'SKF'])->saveQuietly());
    Product::factory()->create(['name' => 'YATO csigafúró készlet 19 részes']);

    get(route('brands'))->assertOk()->assertSee(['Márkáink', '2 termék', '1 termék'])->assertSeeHtml('href="' . route('categories.index', ['marka' => 'SKF']) . '"')->assertSeeHtml('href="' . route('products.index', ['gyarto' => 'YATO']) . '"')->assertDontSeeHtml('href="' . route('categories.index', ['marka' => 'INA']) . '"')
        ->assertSee('Érdeklődjön üzleteinkben');
});

it('shows the brands the client listed, LOCTITE and their last additions, SKF first', function (): void {
    $names = collect(config('brands'))->pluck('name');

    expect($names)->toHaveCount(45)
        ->and($names->first())->toBe('SKF')
        ->and($names)->toContain('LOCTITE', 'KS TOOLS', 'FABORY', 'Rexroth', 'Robur')
        ->and($names)->not->toContain('TIMKEN', 'DURACELL');

    foreach (config('brands') as $brand) {
        expect(resource_path('images/brands/' . $brand['logo']))->toBeFile();
    }
});

it('opens the product list with the brand from the link selected', function (): void {
    $skf = Product::factory()->create(['name' => 'SKF csapágy teszt']);
    $skf->forceFill(['brand' => 'SKF'])->saveQuietly();
    $ina = Product::factory()->create(['name' => 'INA csapágy teszt']);
    $ina->forceFill(['brand' => 'INA'])->saveQuietly();

    Livewire::withQueryParams(['marka' => 'SKF'])
        ->test(CategoriesIndex::class)
        ->assertSet('selectedFilters.brand', ['SKF'])
        ->assertSee('SKF csapágy teszt')
        ->assertDontSee('INA csapágy teszt');
});

it('lists the active catalogues in the admin order', function (): void {
    Catalog::factory()->create(['title' => 'Második', 'sort_order' => 2]);
    Catalog::factory()->create(['title' => 'Első', 'sort_order' => 1]);
    Catalog::factory()->create(['title' => 'Rejtett', 'is_active' => false]);

    get(route('catalogs'))->assertOk()
        ->assertSeeInOrder(['Első', 'Második'])->assertDontSee('Rejtett')->assertSeeHtml(Storage::disk('public')->url(Catalog::query()->where('title', 'Első')->value('file')));
});

it('asks for catalogues by email while none are uploaded', function (): void {
    get(route('catalogs'))->assertOk()->assertSee(['hamarosan', 'gs@gordulo-simmering.hu']);
});

it('lets the admin upload a catalogue', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(ListCatalogs::class)->assertOk();
    Livewire::test(CreateCatalog::class)
        ->fillForm(['title' => 'SKF általános katalógus', 'file' => UploadedFile::fake()->create('skf.pdf', 500, 'application/pdf')])
        ->call('create')
        ->assertHasNoFormErrors();

    $catalog = Catalog::query()->sole();
    Storage::disk('public')->assertExists($catalog->file);
    expect($catalog->is_active)->toBeTrue();
});

it('fills the homepage brand box with 30 logos', function (): void {
    $html = get(route('index'))->assertOk()->getContent();
    $tile = mb_substr($html, mb_strpos($html, '<!-- Brands Card -->'), 20000);
    $tile = mb_substr($tile, 0, mb_strpos($tile, '<!-- Catalogues Card -->'));

    preg_match('/alt="([^"]+)"/', $tile, $firstLogo);

    expect(mb_substr_count($tile, '<img'))->toBe(30)
        ->and($firstLogo[1])->toBe('SKF')
        ->and($tile)->not->toContain('alt="MEGADYNE"', 'alt="FABORY"', 'alt="Robur"');
});

it('leaves the brand category out of the homepage category menu', function (): void {
    $brands = Category::query()->create(['name' => Category::BRAND_ROOT_NAME, 'slug' => 'forgalmazott-markaink']);
    $skf = Category::query()->create(['name' => 'SKF', 'slug' => 'skf', 'category_id' => $brands->id]);
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $skf->products()->attach(Product::factory()->create());
    $bearings->products()->attach(Product::factory()->create());

    $html = get(route('index'))->assertOk()->getContent();
    $menu = mb_substr($html, mb_strpos($html, 'Termékeink</h2>'), 6000);

    expect($menu)->toContain('CSAPÁGYAK')->not->toContain(Category::BRAND_ROOT_NAME);
});

it('counts the brands afresh when the brand list changes', function (): void {
    Product::factory()->create()->forceFill(['brand' => 'SKF'])->saveQuietly();
    get(route('brands'))->assertOk();

    config(['brands' => [...config('brands'), ['name' => 'TIMKEN', 'logo' => 'skf.svg', 'filter' => 'TIMKEN']]]);
    Product::factory()->create()->forceFill(['brand' => 'TIMKEN'])->saveQuietly();

    get(route('brands'))->assertOk()->assertSeeHtml('href="' . route('categories.index', ['marka' => 'TIMKEN']) . '"');
});
