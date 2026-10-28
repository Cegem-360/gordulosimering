<?php

declare(strict_types=1);

use App\Filament\Resources\RecommendationRules\Pages\CreateRecommendationRule;
use App\Filament\Resources\RecommendationRules\Pages\ListRecommendationRules;
use App\Livewire\Products\Show;
use App\Models\Category;
use App\Models\Product;
use App\Models\RecommendationRule;
use App\Models\User;
use App\Services\ProductRecommendations;
use App\Services\RecommendationRuleImporter;
use App\Services\XlsxReader;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

use Tests\TestCase;

/**
 * An .xlsx with the given rows on its first sheet, text cells via shared strings.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function recommendationXlsx(array $rows): string
{
    $strings = [];
    $sheetRows = '';

    foreach ($rows as $rowIndex => $row) {
        $cells = '';

        foreach ($row as $columnIndex => $text) {
            if ($text === '') {
                continue;
            }

            $strings[] = htmlspecialchars($text, ENT_XML1);
            $cells .= sprintf('<c r="%s%d" t="s"><v>%d</v></c>', chr(65 + $columnIndex), $rowIndex + 1, count($strings) - 1);
        }

        $sheetRows .= sprintf('<row r="%d">%s</row>', $rowIndex + 1, $cells);
    }

    $main = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    $path = tempnam(sys_get_temp_dir(), 'xlsx');
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString('xl/workbook.xml', '<workbook xmlns="' . $main . '" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Munka1" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
    $zip->addFromString('xl/sharedStrings.xml', '<sst xmlns="' . $main . '">' . implode('', array_map(fn (string $text): string => "<si><t>{$text}</t></si>", $strings)) . '</sst>');
    $zip->addFromString('xl/worksheets/sheet1.xml', '<worksheet xmlns="' . $main . '"><sheetData>' . $sheetRows . '</sheetData></worksheet>');
    $zip->close();

    return $path;
}

/**
 * @return array{bearings: Category, ballBearings: Category, accessories: Category, circlips: Category, pliers: Category, chemicals: Category, cleaning: Category}
 */
function recommendationCategories(): array
{
    $bearings = Category::factory()->create(['name' => 'CSAPÁGYAK']);
    $accessories = Category::factory()->create(['name' => 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK']);
    $circlips = Category::factory()->childOf($accessories)->create(['name' => 'SEEGER GYŰRŰ']);
    $chemicals = Category::factory()->create(['name' => 'VEGYI ÁRUK']);

    return [
        'bearings' => $bearings,
        'ballBearings' => Category::factory()->childOf($bearings)->create(['name' => 'GOLYÓS CSAPÁGY']),
        'accessories' => $accessories,
        'circlips' => $circlips,
        'pliers' => Category::factory()->childOf($circlips)->create(['name' => 'SEEGER FOGÓ']),
        'chemicals' => $chemicals,
        'cleaning' => Category::factory()->childOf($chemicals)->create(['name' => 'TISZTÍTÁS, ZSÍRTALANÍTÁS']),
    ];
}

function productIn(Category $category, array $attributes = []): Product
{
    $product = Product::factory()->create($attributes);
    $product->categories()->attach($category);

    return $product;
}

it('reads the cell texts of the first worksheet', function (): void {
    $path = recommendationXlsx([['Ezt vásárolják:', '', '', 'Ezt kínáljuk mellé:'], ['MINDENHEZ', '', '', 'VEGYI ÁRUK', 'TISZTÍTÁS, ZSÍRTALANÍTÁS']]);

    expect(resolve(XlsxReader::class)->rows($path))->toBe([
        ['Ezt vásárolják:', '', '', 'Ezt kínáljuk mellé:'],
        ['MINDENHEZ', '', '', 'VEGYI ÁRUK', 'TISZTÍTÁS, ZSÍRTALANÍTÁS'],
    ]);
});

it('imports the four kinds of rows of the client\'s sheet and skips what it already has', function (): void {
    $categories = recommendationCategories();
    $path = recommendationXlsx([
        ['Ezt vásárolják:', '', '', 'Ezt kínáljuk mellé:'],
        ['MINDENHEZ', '', '', 'VEGYI ÁRUK', 'TISZTÍTÁS, ZSÍRTALANÍTÁS'],
        ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ'],
        ['CSAPÁGYAK', '', '', 'TMAS'],
        ['CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', 'SEEGER FOGÓ'],
        ['CSAPÁGYAK', 'NINCS ILYEN', '', 'VEGYI ÁRUK'],
    ]);

    $importer = resolve(RecommendationRuleImporter::class);

    expect($importer->import($path))->toBe(['created' => 4, 'skipped' => 0, 'unresolved' => ['CSAPÁGYAK / NINCS ILYEN → VEGYI ÁRUK']])
        ->and(RecommendationRule::query()->get(['source_category_id', 'target_category_id', 'target_code_prefix'])->toArray())->toBe([
            ['source_category_id' => null, 'target_category_id' => $categories['cleaning']->id, 'target_code_prefix' => null],
            ['source_category_id' => $categories['ballBearings']->id, 'target_category_id' => $categories['circlips']->id, 'target_code_prefix' => null],
            ['source_category_id' => $categories['bearings']->id, 'target_category_id' => null, 'target_code_prefix' => 'TMAS'],
            ['source_category_id' => $categories['circlips']->id, 'target_category_id' => $categories['pliers']->id, 'target_code_prefix' => null],
        ])
        ->and($importer->import($path)['skipped'])->toBe(4);
});

it('offers products by the rules of the product\'s category and its parents', function (): void {
    $categories = recommendationCategories();
    $bearing = productIn($categories['ballBearings']);
    $circlip = productIn($categories['circlips']);
    $plier = productIn($categories['pliers']);
    $cleaner = productIn($categories['cleaning']);
    $shim = Product::factory()->create(['product_code' => 'TMAS 100-005_S']);
    Product::factory()->create(['product_code' => 'XTMAS 1']);
    productIn($categories['chemicals']);

    RecommendationRule::factory()->forEverything()->create(['target_category_id' => $categories['cleaning']->id]);
    RecommendationRule::factory()->create(['source_category_id' => $categories['ballBearings']->id, 'target_category_id' => $categories['circlips']->id]);
    RecommendationRule::factory()->codePrefix('TMAS')->create(['source_category_id' => $categories['bearings']->id]);
    RecommendationRule::factory()->create(['source_category_id' => $categories['circlips']->id, 'target_category_id' => $categories['chemicals']->id, 'is_active' => false]);

    expect(resolve(ProductRecommendations::class)->for($bearing)->modelKeys())
        ->toEqualCanonicalizing([$circlip->id, $plier->id, $cleaner->id, $shim->id]);
});

it('leaves out the product itself and hidden products, puts in-stock ones first, and stops at eight', function (): void {
    $categories = recommendationCategories();
    $cleaner = productIn($categories['cleaning']);
    $outOfStock = productIn($categories['circlips'], ['stock_quantity' => 0]);
    productIn($categories['circlips'], ['is_web_visible' => false]);
    $inStock = collect(range(1, 9))->map(fn (): Product => productIn($categories['circlips'], ['stock_quantity' => 5]));

    RecommendationRule::factory()->forEverything()->create(['target_category_id' => $categories['accessories']->id]);

    $recommended = resolve(ProductRecommendations::class)->for($cleaner);

    expect($recommended)->toHaveCount(8)
        ->and($recommended->modelKeys())->not->toContain($outOfStock->id)
        ->and($inStock->pluck('id')->all())->toContain(...$recommended->modelKeys());
});

it('gives every matching rule a place before a rule gets a second one', function (): void {
    $categories = recommendationCategories();
    $bearing = productIn($categories['ballBearings']);
    $cleaners = collect(range(1, 8))->map(fn (): Product => productIn($categories['cleaning']));
    $circlips = collect(range(1, 8))->map(fn (): Product => productIn($categories['circlips']));

    RecommendationRule::factory()->forEverything()->create(['target_category_id' => $categories['cleaning']->id]);
    RecommendationRule::factory()->create(['source_category_id' => $categories['ballBearings']->id, 'target_category_id' => $categories['circlips']->id]);

    $keys = resolve(ProductRecommendations::class)->for($bearing)->modelKeys();

    expect(array_intersect($keys, $cleaners->pluck('id')->all()))->toHaveCount(4)
        ->and(array_intersect($keys, $circlips->pluck('id')->all()))->toHaveCount(4);
});

it('keeps the pick for the week and picks again when the rules change', function (): void {
    /** @var TestCase $this */
    $categories = recommendationCategories();
    $bearing = productIn($categories['ballBearings']);
    $first = productIn($categories['cleaning']);
    $rule = RecommendationRule::factory()->forEverything()->create(['target_category_id' => $categories['cleaning']->id]);
    $service = resolve(ProductRecommendations::class);

    expect($service->for($bearing)->modelKeys())->toBe([$first->id]);

    $circlip = productIn($categories['circlips']);
    expect($service->for($bearing)->modelKeys())->toBe([$first->id]);

    $this->travel(1)->seconds();
    $rule->update(['target_category_id' => $categories['circlips']->id]);
    expect($service->for($bearing)->modelKeys())->toBe([$circlip->id]);
});

it('shows the row on the product page only when there is something to offer', function (): void {
    $categories = recommendationCategories();
    $bearing = productIn($categories['ballBearings']);
    $cleaner = productIn($categories['cleaning'], ['name' => 'MOTIP féktisztító spray']);

    Livewire::test(Show::class, ['product' => $bearing])->assertDontSee('Gyakran együtt vásárolják');

    RecommendationRule::factory()->forEverything()->create(['target_category_id' => $categories['cleaning']->id]);

    get(route('products.show', $bearing))->assertOk()->assertSee(['Gyakran együtt vásárolják', $cleaner->name]);
});

it('lets the admin import the sheet and add a rule with one target', function (): void {
    $categories = recommendationCategories();
    actingAs(User::factory()->create(['is_admin' => true]));

    $sheet = recommendationXlsx([['Ezt vásárolják:'], ['MINDENHEZ', '', '', 'VEGYI ÁRUK', 'TISZTÍTÁS, ZSÍRTALANÍTÁS']]);

    Livewire::test(ListRecommendationRules::class)
        ->assertOk()
        ->callAction('import', ['file' => UploadedFile::fake()->createWithContent('gyakran-veszik-egyutt.xlsx', (string) file_get_contents($sheet))])
        ->assertNotified('Az importálás kész.');

    expect(RecommendationRule::query()->where('target_category_id', $categories['cleaning']->id)->exists())->toBeTrue();

    Livewire::test(CreateRecommendationRule::class)
        ->fillForm(['source_category_id' => $categories['bearings']->id])
        ->call('create')
        ->assertHasFormErrors(['target_category_id' => 'required_without']);

    Livewire::test(CreateRecommendationRule::class)
        ->fillForm(['source_category_id' => $categories['bearings']->id, 'target_code_prefix' => 'HNA'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(RecommendationRule::query()->where('target_code_prefix', 'HNA')->value('source_category_id'))->toBe($categories['bearings']->id);
});
