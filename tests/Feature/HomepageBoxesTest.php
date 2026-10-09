<?php

declare(strict_types=1);

use App\Filament\Resources\Catalogs\Pages\CreateCatalog;
use App\Filament\Resources\Catalogs\Pages\ListCatalogs;
use App\Livewire\Products\Categories\Index as CategoriesIndex;
use App\Models\Catalog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('links the new homepage boxes to Márkáink, Katalógusok and the delivery section', function (): void {
    get(route('index'))->assertOk()
        ->assertSee(['Márkák', 'Katalógusok', 'Szállítási költségek'])
        ->assertSee('href="' . route('brands') . '"', false)
        ->assertSee('href="' . route('catalogs') . '"', false)
        ->assertSee('href="' . route('services') . '#hazhozszallitas"', false);
});

it('links each brand with products to the product list filtered by it', function (): void {
    $skf = Product::factory()->count(2)->create();
    $skf->each(fn (Product $product) => $product->forceFill(['brand' => 'SKF'])->saveQuietly());

    get(route('brands'))->assertOk()
        ->assertSee(['Márkáink', '2 termék'])
        ->assertSee('href="' . route('categories.index', ['marka' => 'SKF']) . '"', false)
        ->assertDontSee('href="' . route('categories.index', ['marka' => 'INA']) . '"', false)
        ->assertSee('Érdeklődjön üzleteinkben');
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
        ->assertSeeInOrder(['Első', 'Második'])
        ->assertDontSee('Rejtett')
        ->assertSee(Storage::disk('public')->url(Catalog::query()->where('title', 'Első')->value('file')), false);
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
