# Attribútumszűrők – megvalósítási terv

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A `/termekkategoriak` és a `/termekek` szűrősávja kapjon Termékcsoport, Méretek (mm, tól–ig), Márka és Anyag szűrőt, amelyek a termék nevéből, méretéből és Integra-csoportjából kiszámolt adatokra épülnek.

**Architecture:** Egy tiszta `ProductAttributeExtractor` szolgáltatás a `name`/`size` mezőkből öt indexelt oszlopot számol (`inner_diameter`, `outer_diameter`, `width`, `brand`, `material`); a `Product` `saving` eseménye frissen tartja őket, egy artisan parancs visszamenőleg tölti. A közös `FiltersProducts` Livewire trait bővül az új szűrőkkel, a szűrősáv komponens egy új „range” szekciótípust kap.

**Tech Stack:** PHP 8.4, Laravel 13, Livewire 4, Pest 4, Tailwind 4, SQLite (lokál/teszt) és MySQL (éles).

**Spec:** `docs/superpowers/specs/2026-09-29-attribute-filters-design.md`

## Global Constraints

- Kódstílus: `declare(strict_types=1);`, `final class`, explicit visszatérési típusok, PHPDoc tömbalakokkal; futtasd a `vendor/bin/pint --dirty`-t minden task végén.
- Felhasználói szövegek magyarul; kód- és tesztnevek angolul, a meglévő fájlok mintájára.
- Új fájlokat `php artisan make:… --no-interaction`-nel hozz létre.
- Nincs új Composer/npm függőség.
- A darabszámok a szűretlen alaplistából számolódnak (`filterableProducts()`), nem a többi kiválasztott szűrő szerint.
- A „Megszűnt termék” nevű termékcsoport nem jelenik meg a szűrőben.
- Push a `main`-re = éles deploy: csak a felhasználó kifejezett engedélyével pusholj.

## Review Focus

1. **Nem szám / negatív / vesszős tartományhatár** (`"abc"`, `"-5"`, `"25,4"`, `""`) – a vevő elgépeli: a rossz határ figyelmen kívül marad, a `"25,4"` 25.4-ként számít. → Task 4 teszt: *ignores invalid bounds and accepts a decimal comma*.
2. **Fordított tartomány** (tól 30, ig 20) – a vevő azt várja, hogy 20 és 30 közötti termékeket lát, ne üres listát: a két határ felcserélődik. → Task 4 teszt: *swaps a reversed range*.
3. **Lapozás szűrőváltáskor** – a 3. oldalon állva tartományt ad meg: az első oldalra kell ugrani, különben üres oldalt lát. → Task 4 teszt: *goes back to the first page when a range changes*.
4. **Integra-szinkron után frissülő attribútum** – az ERP-ben átírják a nevet vagy a méretet: a márka/méret oszlopnak követnie kell (a szinkron csak néhány oszlopot tölt be). → Task 2 teszt: *recalculates the attributes when the Integra7 sync changes the name or size*.
5. **Null név vagy méret** (factory-k, régi sorok) – mentéskor nem dobhat hibát, az oszlopok üresek maradnak. → Task 1 dataset (`null` sorok) és Task 2 teszt: *saves a product without name or size*.

---

## Fájlszerkezet

| Fájl | Felelősség |
|---|---|
| Create `app/Services/ProductAttributeExtractor.php` | Tiszta függvény: `name` + `size` → öt attribútum |
| Create `tests/Unit/Services/ProductAttributeExtractorTest.php` | A kinyerési szabályok datasetekkel |
| Create `database/migrations/*_add_attribute_columns_to_products_table.php` | Öt új, indexelt oszlop |
| Modify `app/Models/Product.php` | `booted()` saving hook, castok |
| Create `app/Console/Commands/ExtractProductAttributesCommand.php` | Visszamenőleges feltöltés |
| Create `tests/Feature/ProductAttributesTest.php` | Mentés, szinkron, parancs |
| Modify `app/Livewire/Concerns/FiltersProducts.php` | Új szűrők állapota, opciói, lekérdezése |
| Modify `resources/views/components/categories/filter-sidebar.blade.php` | „range” szekció megjelenítése |
| Modify `resources/views/livewire/products/categories/index.blade.php` | Tartomány-címkék |
| Modify `resources/views/livewire/products/index.blade.php` | Tartomány-címkék |
| Modify `tests/Feature/Livewire/Products/Categories/IndexTest.php` | Szűrőkulcsok frissítése, új szűrőtesztek |

---

### Task 1: ProductAttributeExtractor

**Files:**
- Create: `app/Services/ProductAttributeExtractor.php`
- Test: `tests/Unit/Services/ProductAttributeExtractorTest.php`

**Interfaces:**
- Produces:
  - `App\Services\ProductAttributeExtractor::extract(?string $name, ?string $size): array{inner_diameter: ?float, outer_diameter: ?float, width: ?float, brand: ?string, material: ?string}`
  - `ProductAttributeExtractor::COLUMNS` = `['inner_diameter', 'outer_diameter', 'width', 'brand', 'material']` (public const)

- [ ] **Step 1: Hozd létre a tesztfájlt**

Run: `php artisan make:test --pest --unit Services/ProductAttributeExtractorTest --no-interaction`

Tartalma:

```php
<?php

declare(strict_types=1);

use App\Services\ProductAttributeExtractor;

it('reads the dimensions from the start of the size', function (?string $size, ?float $inner, ?float $outer, ?float $width): void {
    expect((new ProductAttributeExtractor())->extract(null, $size))
        ->inner_diameter->toBe($inner)
        ->outer_diameter->toBe($outer)
        ->width->toBe($width);
})->with([
    'three parts' => ['25X47X8', 25.0, 47.0, 8.0],
    'lower-case x' => ['20x47x14', 20.0, 47.0, 14.0],
    'decimal commas' => ['60,32X82,55X9,52', 60.32, 82.55, 9.52],
    'decimal points' => ['25.4X50.8X15', 25.4, 50.8, 15.0],
    'double width' => ['65X85X7/7,5', 65.0, 85.0, 7.0],
    'trailing word' => ['25X62X25 DOMBORÚ', 25.0, 62.0, 25.0],
    'trailing reference' => ['100X157X42/34 (VKHB 2032)', 100.0, 157.0, 42.0],
    'two parts keep only the bore' => ['18X1,2', 18.0, null, null],
    'unfinished third part' => ['85,725X136,525X', 85.725, null, null],
    'third part is not a number' => ['50X110X M24', 50.0, null, null],
    'free text' => ['A28,5', null, null, null],
    'code in front' => ['12655 10X300', null, null, null],
    'belt profile' => ['PHP 4SPA315TB', null, null, null],
    'empty' => ['', null, null, null],
    'null' => [null, null, null, null],
]);

it('takes the brand from the first word of the name', function (?string $name, ?string $brand): void {
    expect((new ProductAttributeExtractor())->extract($name, null)['brand'])->toBe($brand);
})->with([
    'SKF' => ['SKF egysorú mélyhornyú golyóscsapágy', 'SKF'],
    'SKF/Ewellix' => ['SKF/Ewellix lineáris vezeték', 'SKF'],
    'ZKL/ZVL' => ['ZKL/ZVL hengergörgős csapágy', 'ZKL'],
    'lower-case first word' => ['koyo kúpgörgős csapágy', 'KOYO'],
    'comma after the brand' => ['SEEGER, rögzítő gyűrű', 'SEEGER'],
    'KELETI is not a brand' => ['KELETI golyóscsapágy', null],
    'plain description' => ['Gumiházas simmering, NBR', null],
    'brand later in the name' => ['Csapágy SKF', null],
    'empty' => ['', null],
    'null' => [null, null],
]);

it('finds the material in the name', function (?string $name, ?string $material): void {
    expect((new ProductAttributeExtractor())->extract($name, null)['material'])->toBe($material);
})->with([
    'NBR' => ['Gumiházas simmering, NBR', 'NBR'],
    'VITON' => ['Gumiházas simmering, VITON', 'FKM (Viton)'],
    'FKM' => ['O-gyűrű FKM 80', 'FKM (Viton)'],
    'EPDM' => ['O-gyűrű EPDM', 'EPDM'],
    'PTFE' => ['PTFE tömítőgyűrű', 'PTFE'],
    'stainless' => ['SEEGER rögzítő gyűrű DIN471 rozsdamentes', 'Rozsdamentes'],
    'stainless capitalised' => ['Rozsdamentes golyóscsapágy', 'Rozsdamentes'],
    'only whole words' => ['HNBRX tömítés', null],
    'first in the list wins' => ['Simmering NBR/VITON', 'NBR'],
    'none' => ['SKF golyóscsapágy', null],
    'null' => [null, null],
]);
```

- [ ] **Step 2: Futtasd, hogy elbukik**

Run: `php artisan test tests/Unit/Services/ProductAttributeExtractorTest.php`
Expected: FAIL – `Class "App\Services\ProductAttributeExtractor" not found`.

- [ ] **Step 3: Írd meg a szolgáltatást**

Run: `php artisan make:class Services/ProductAttributeExtractor --no-interaction`, majd a tartalma:

```php
<?php

declare(strict_types=1);

namespace App\Services;

/**
 * A termék nevéből és ERP-s méretéből kiolvasható szűrési adatok: a
 * „d×D×B” méret számai, a név elején álló márka és a névben szereplő anyag.
 * Kételemű méretnél csak a belső átmérő megbízható, mert a második szám
 * termékenként mást jelent (vastagság vagy külső átmérő).
 */
final class ProductAttributeExtractor
{
    /**
     * @var array<int, string>
     */
    public const array COLUMNS = ['inner_diameter', 'outer_diameter', 'width', 'brand', 'material'];

    /**
     * @var array<int, string>
     */
    private const array BRANDS = [
        'SKF', 'KOYO', 'INA', 'FAG', 'ZKL', 'NTN', 'TIMKEN', 'NSK', 'IKO', 'SNR', 'CORTECO', 'SIMRIT',
        'SEEGER', 'LOCTITE', 'NORMA', 'TENTE', 'EZO', 'NACHI', 'REXROTH', 'HIWIN', 'BETA', 'BAHCO', 'AMES',
        'NILOS', 'BECO', 'KS', 'WSW', 'STIEBER', 'DURACELL',
    ];

    /**
     * Regex alternative => a szűrőben megjelenő anyag; a lista sorrendje dönt.
     *
     * @var array<string, string>
     */
    private const array MATERIALS = [
        'NBR' => 'NBR',
        'VITON|FKM' => 'FKM (Viton)',
        'EPDM' => 'EPDM',
        'PTFE' => 'PTFE',
        'rozsdamentes' => 'Rozsdamentes',
    ];

    /**
     * @return array{inner_diameter: ?float, outer_diameter: ?float, width: ?float, brand: ?string, material: ?string}
     */
    public function extract(?string $name, ?string $size): array
    {
        return [
            ...$this->dimensions($size),
            'brand' => $this->brand($name),
            'material' => $this->material($name),
        ];
    }

    /**
     * @return array{inner_diameter: ?float, outer_diameter: ?float, width: ?float}
     */
    private function dimensions(?string $size): array
    {
        $number = '(\d+(?:[.,]\d+)?)';

        if (preg_match("/^\\s*{$number}\\s*x\\s*{$number}(?:\\s*x\\s*{$number})?/iu", (string) $size, $matches) !== 1) {
            return ['inner_diameter' => null, 'outer_diameter' => null, 'width' => null];
        }

        $hasThreeParts = isset($matches[3]);

        return [
            'inner_diameter' => $this->toNumber($matches[1]),
            'outer_diameter' => $hasThreeParts ? $this->toNumber($matches[2]) : null,
            'width' => $hasThreeParts ? $this->toNumber($matches[3]) : null,
        ];
    }

    private function brand(?string $name): ?string
    {
        $firstWord = preg_split('/[\s,]+/u', mb_trim((string) $name), 2)[0] ?? '';
        $candidate = mb_strtoupper(explode('/', $firstWord)[0]);

        return in_array($candidate, self::BRANDS, true) ? $candidate : null;
    }

    private function material(?string $name): ?string
    {
        foreach (self::MATERIALS as $pattern => $material) {
            if (preg_match("/(?<![\\p{L}\\d])(?:{$pattern})(?![\\p{L}\\d])/iu", (string) $name) === 1) {
                return $material;
            }
        }

        return null;
    }

    private function toNumber(string $value): float
    {
        return (float) str_replace(',', '.', $value);
    }
}
```

- [ ] **Step 4: Futtasd, hogy átmegy**

Run: `php artisan test tests/Unit/Services/ProductAttributeExtractorTest.php`
Expected: PASS (36 eset).

- [ ] **Step 5: Pint és commit**

```bash
vendor/bin/pint --dirty
git add app/Services/ProductAttributeExtractor.php tests/Unit/Services/ProductAttributeExtractorTest.php
git commit -m "Extract the dimensions, brand and material from the product name and size"
```

---

### Task 2: Oszlopok, mentéskori számolás, visszamenőleges parancs

**Files:**
- Create: `database/migrations/<timestamp>_add_attribute_columns_to_products_table.php`
- Modify: `app/Models/Product.php` (új `booted()`, `casts()` bővítése)
- Create: `app/Console/Commands/ExtractProductAttributesCommand.php`
- Test: `tests/Feature/ProductAttributesTest.php`

**Interfaces:**
- Consumes: `ProductAttributeExtractor::extract()`, `ProductAttributeExtractor::COLUMNS` (Task 1)
- Produces: `products.inner_diameter|outer_diameter|width` (float cast, nullable), `products.brand|material` (string, nullable); artisan `app:extract-product-attributes`, kimenete `Frissítve: {n} termék.`

- [ ] **Step 1: Írd meg a feature tesztet**

Run: `php artisan make:test --pest ProductAttributesTest --no-interaction`, tartalma:

```php
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
```

- [ ] **Step 2: Futtasd, hogy elbukik**

Run: `php artisan test tests/Feature/ProductAttributesTest.php`
Expected: FAIL – `no such column: inner_diameter` (vagy null attribútumok).

- [ ] **Step 3: Migráció**

Run: `php artisan make:migration add_attribute_columns_to_products_table --table=products --no-interaction`, tartalma:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A szűrősáv attribútumai, a névből és a méretből számolva
 * (ProductAttributeExtractor); az app:extract-product-attributes tölti fel.
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('inner_diameter', 10, 3)->nullable()->after('size')->index();
            $table->decimal('outer_diameter', 10, 3)->nullable()->after('inner_diameter')->index();
            $table->decimal('width', 10, 3)->nullable()->after('outer_diameter')->index();
            $table->string('brand', 50)->nullable()->after('width')->index();
            $table->string('material', 50)->nullable()->after('brand')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['inner_diameter']);
            $table->dropIndex(['outer_diameter']);
            $table->dropIndex(['width']);
            $table->dropIndex(['brand']);
            $table->dropIndex(['material']);
            $table->dropColumn(['inner_diameter', 'outer_diameter', 'width', 'brand', 'material']);
        });
    }
};
```

- [ ] **Step 4: Mentéskori számolás és castok a `Product`-on**

Az `app/Models/Product.php`-ban importáld: `use App\Services\ProductAttributeExtractor;`. A `resolveCustomerDiscountUsing()` után vedd fel:

```php
    #[Override]
    protected static function booted(): void
    {
        self::saving(function (Product $product): void {
            if (! $product->exists || $product->isDirty(['name', 'size'])) {
                $product->fill(resolve(ProductAttributeExtractor::class)->extract($product->name, $product->size));
            }
        });
    }
```

A `casts()` tömbjébe a `'weight' => 'decimal:3',` sor után:

```php
            'inner_diameter' => 'float',
            'outer_diameter' => 'float',
            'width' => 'float',
```

- [ ] **Step 5: A visszamenőleges parancs**

Run: `php artisan make:command ExtractProductAttributesCommand --no-interaction`, tartalma:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductAttributeExtractor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

#[Description('A termékek méret-, márka- és anyagadatainak kiszámolása a névből és a méretből')]
#[Signature('app:extract-product-attributes')]
final class ExtractProductAttributesCommand extends Command
{
    private const int CHUNK_SIZE = 1000;

    public function handle(ProductAttributeExtractor $extractor): int
    {
        $updated = 0;

        Product::query()
            ->select(['id', 'name', 'size', ...ProductAttributeExtractor::COLUMNS])
            ->chunkById(self::CHUNK_SIZE, function (Collection $products) use ($extractor, &$updated): void {
                foreach ($products as $product) {
                    $product->fill($extractor->extract($product->name, $product->size));

                    if ($product->isDirty()) {
                        $product->save();
                        $updated++;
                    }
                }
            });

        $this->info("Frissítve: {$updated} termék.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 6: Futtasd a teszteket**

Run: `php artisan test tests/Feature/ProductAttributesTest.php tests/Feature/Integra7SyncerTest.php tests/Unit/Services`
Expected: PASS.

- [ ] **Step 7: Pint és commit**

```bash
vendor/bin/pint --dirty
git add database/migrations app/Models/Product.php app/Console/Commands/ExtractProductAttributesCommand.php tests/Feature/ProductAttributesTest.php
git commit -m "Store the extracted product attributes and backfill them with a command"
```

---

### Task 3: Termékcsoport, Márka és Anyag szűrő

**Files:**
- Modify: `app/Livewire/Concerns/FiltersProducts.php`
- Test: `tests/Feature/Livewire/Products/Categories/IndexTest.php`

**Interfaces:**
- Consumes: `products.brand`, `products.material` (Task 2); `App\Models\DiscountGroup` (`code`, `name`), `products.group_code`
- Produces: `selectedFilters` új kulcsai `group`, `brand`, `material` (`array<int, string>`); a `filters()` elemek kulcssorrendje ebben a taskban `['stock', 'category', 'group', 'size', 'brand', 'material']` (a Task 4 szúrja be a `dimensions`-t a `group` után)

- [ ] **Step 1: Frissítsd a meglévő kulcstesztet és írd meg az új teszteket**

A `tests/Feature/Livewire/Products/Categories/IndexTest.php`-ban az `offers stock, category and size filters but no quality filter` teszt helyett:

```php
it('offers the stock, category, group, size, brand and material filters but no quality filter', function (string $component): void {
    Livewire::test($component)
        ->assertSee(['Készlet', 'Kategória', 'Termékcsoport', 'Méret', 'Márka', 'Anyag'])
        ->assertDontSee('Minőség');

    expect(collect(Livewire::test($component)->instance()->filters)->pluck('key')->all())
        ->toBe(['stock', 'category', 'group', 'size', 'brand', 'material']);
})->with([
    'category index' => [Index::class],
    'product list' => [ProductsIndex::class],
]);
```

A fájl végére (importáld: `use App\Models\DiscountGroup;`):

```php
it('lists the brands and materials by how many products have them', function (): void {
    Product::factory()->count(2)->create(['name' => 'SKF simmering, NBR']);
    Product::factory()->create(['name' => 'KOYO simmering, VITON']);
    Product::factory()->create(['name' => 'Gumiházas simmering']);
    Product::factory()->create(['name' => 'INA csapágy', 'is_web_visible' => false]);

    $filters = collect(Livewire::test(Index::class)->instance()->filters);

    expect($filters->firstWhere('key', 'brand')['items'])->toBe([
        ['name' => 'SKF', 'value' => 'SKF', 'count' => 2],
        ['name' => 'KOYO', 'value' => 'KOYO', 'count' => 1],
    ])->and($filters->firstWhere('key', 'material')['items'])->toBe([
        ['name' => 'NBR', 'value' => 'NBR', 'count' => 2],
        ['name' => 'FKM (Viton)', 'value' => 'FKM (Viton)', 'count' => 1],
    ]);
});

it('filters by brand and by material', function (string $key, string $value): void {
    $skfNbr = Product::factory()->create(['name' => 'SKF simmering, NBR']);
    Product::factory()->create(['name' => 'KOYO simmering, VITON']);

    $component = Livewire::test(Index::class)->set("selectedFilters.{$key}", [$value]);

    expect($component->instance()->products->pluck('id')->all())->toBe([$skfNbr->id]);
})->with([
    'brand' => ['brand', 'SKF'],
    'material' => ['material', 'NBR'],
]);

it('merges the product groups that share a name and leaves out the discontinued and unnamed ones', function (): void {
    DiscountGroup::factory()->create(['code' => 'S2', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'S5', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'CT', 'name' => 'Tőkés (minőségi) csapágy']);
    DiscountGroup::factory()->create(['code' => 'PM', 'name' => 'Megszűnt termék']);
    DiscountGroup::factory()->create(['code' => 'XX', 'name' => null]);
    DiscountGroup::factory()->create(['code' => 'EK', 'name' => 'Szíjhajtások']);
    Product::factory()->create(['group_code' => 'S2']);
    Product::factory()->count(2)->create(['group_code' => 'S5']);
    Product::factory()->create(['group_code' => 'CT']);
    Product::factory()->create(['group_code' => 'PM']);
    Product::factory()->create(['group_code' => 'XX']);

    $groups = collect(Livewire::test(Index::class)->instance()->filters)->firstWhere('key', 'group')['items'];

    expect($groups)->toBe([
        ['name' => 'SKF csapágy', 'value' => 'SKF csapágy', 'count' => 3],
        ['name' => 'Tőkés (minőségi) csapágy', 'value' => 'Tőkés (minőségi) csapágy', 'count' => 1],
    ]);
});

it('filters by a product group name across all of its codes and labels the chip with it', function (): void {
    DiscountGroup::factory()->create(['code' => 'S2', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'S5', 'name' => 'SKF csapágy']);
    DiscountGroup::factory()->create(['code' => 'CT', 'name' => 'Tőkés (minőségi) csapágy']);
    $s2 = Product::factory()->create(['group_code' => 'S2', 'name' => 'A']);
    $s5 = Product::factory()->create(['group_code' => 'S5', 'name' => 'B']);
    Product::factory()->create(['group_code' => 'CT']);

    $component = Livewire::test(Index::class)->set('selectedFilters.group', ['SKF csapágy']);

    expect($component->instance()->products->pluck('id')->sort()->values()->all())->toBe([$s2->id, $s5->id])
        ->and($component->html())->toContain('wire:key="chip-group-SKF csapágy"');
});

it('clears the new filters with "Szűrők törlése"', function (): void {
    $component = Livewire::test(Index::class)
        ->set('selectedFilters.brand', ['SKF'])
        ->set('selectedFilters.material', ['NBR'])
        ->set('selectedFilters.group', ['SKF csapágy'])
        ->call('clearFilters');

    expect($component->get('selectedFilters'))
        ->brand->toBe([])
        ->material->toBe([])
        ->group->toBe([]);
});
```

- [ ] **Step 2: Futtasd, hogy elbukik**

Run: `php artisan test tests/Feature/Livewire/Products/Categories/IndexTest.php`
Expected: FAIL – a kulcsok még `['stock', 'category', 'size']`, a `selectedFilters.brand` nem létezik.

- [ ] **Step 3: Bővítsd a traitet**

`app/Livewire/Concerns/FiltersProducts.php`:

Importok: `use App\Models\DiscountGroup;`.

Az osztály docblockja első mondatát cseréld: `The filter sidebar shared by the product list and the category index: stock, the real top-level categories, product groups, sizes, brands and materials.`

Konstans a `SIZE_OPTION_LIMIT` alá:

```php
    private const string DISCONTINUED_GROUP_NAME = 'Megszűnt termék';
```

A `$selectedFilters` helyett:

```php
    /** @var array{category: array<int, string>, group: array<int, string>, size: array<int, string>, brand: array<int, string>, material: array<int, string>, stock: array<int, string>} */
    public array $selectedFilters = [
        'category' => [],
        'group' => [],
        'size' => [],
        'brand' => [],
        'material' => [],
        'stock' => [],
    ];
```

A `filters()` visszatérési tömbjében a Kategória elem után:

```php
            [
                'title' => 'Termékcsoport',
                'key' => 'group',
                'visible' => 5,
                'items' => $this->groupOptions(),
            ],
```

és a Méret elem után:

```php
            [
                'title' => 'Márka',
                'key' => 'brand',
                'visible' => 5,
                'items' => $this->columnOptions('brand'),
            ],
            [
                'title' => 'Anyag',
                'key' => 'material',
                'visible' => 5,
                'items' => $this->columnOptions('material'),
            ],
```

A `resetProductFilters()` első sora:

```php
        $this->selectedFilters = ['category' => [], 'group' => [], 'size' => [], 'brand' => [], 'material' => [], 'stock' => []];
```

Az `applySelectedFilters()`-ben a méret-feltétel után:

```php
        if ($this->selectedFilters['group'] !== []) {
            $query->whereIn('group_code', DiscountGroup::query()->whereIn('name', $this->selectedFilters['group'])->pluck('code'));
        }

        foreach (['brand', 'material'] as $column) {
            if ($this->selectedFilters[$column] !== []) {
                $query->whereIn($column, $this->selectedFilters[$column]);
            }
        }
```

Új privát metódusok a `categoryOptions()` után:

```php
    /**
     * The named product groups, those sharing a name merged into one option.
     *
     * @return array<int, array{name: string, value: string, count: int}>
     */
    private function groupOptions(): array
    {
        $countsByCode = $this->filterableProducts()
            ->select('group_code', DB::raw('count(*) as count'))
            ->whereNotNull('group_code')
            ->groupBy('group_code')
            ->get()
            ->mapWithKeys(fn (Product $row): array => [$row->group_code => (int) $row->getAttribute('count')]);

        return DiscountGroup::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->where('name', '!=', self::DISCONTINUED_GROUP_NAME)
            ->get(['code', 'name'])
            ->groupBy('name')
            ->map(fn (Collection $groups, string $name): array => [
                'name' => $name,
                'value' => $name,
                'count' => $groups->sum(fn (DiscountGroup $group): int => $countsByCode->get($group->code, 0)),
            ])
            ->filter(fn (array $option): bool => $option['count'] > 0)
            ->sortBy([['count', 'desc'], ['name', 'asc']])
            ->values()
            ->all();
    }

    /**
     * The values of a plain attribute column, the most common first.
     *
     * @return array<int, array{name: string, value: string, count: int}>
     */
    private function columnOptions(string $column): array
    {
        return $this->filterableProducts()
            ->select($column, DB::raw('count(*) as count'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('count')
            ->orderBy($column)
            ->get()
            ->map(fn (Product $row): array => [
                'name' => (string) $row->getAttribute($column),
                'value' => (string) $row->getAttribute($column),
                'count' => (int) $row->getAttribute('count'),
            ])
            ->all();
    }
```

Importáld a `use Illuminate\Support\Collection;`-t is (a `groupBy` eredménye `Illuminate\Database\Eloquent\Collection`, amely ennek alosztálya).

- [ ] **Step 4: Futtasd a teszteket**

Run: `php artisan test tests/Feature/Livewire/Products tests/Feature/Livewire/ProductVisibilityTest.php`
Expected: PASS.

- [ ] **Step 5: Pint és commit**

```bash
vendor/bin/pint --dirty
git add app/Livewire/Concerns/FiltersProducts.php tests/Feature/Livewire/Products/Categories/IndexTest.php
git commit -m "Filter the product lists by product group, brand and material"
```

---

### Task 4: Méretek (mm) tartományszűrő

**Files:**
- Modify: `app/Livewire/Concerns/FiltersProducts.php`
- Modify: `resources/views/components/categories/filter-sidebar.blade.php`
- Modify: `resources/views/livewire/products/categories/index.blade.php`
- Modify: `resources/views/livewire/products/index.blade.php`
- Test: `tests/Feature/Livewire/Products/Categories/IndexTest.php`

**Interfaces:**
- Consumes: `products.inner_diameter|outer_diameter|width` (Task 2); a Task 3-as `filters()` szerkezet
- Produces:
  - `public array $dimensionRanges` – `array<string, array{min: mixed, max: mixed}>`, kulcsok `inner_diameter`, `outer_diameter`, `width`
  - `clearDimensionRange(string $dimension): void`
  - `#[Computed] dimensionRangeChips(): array<int, array{key: string, label: string}>`
  - `filters()` új eleme: `['title' => 'Méretek (mm)', 'key' => 'dimensions', 'type' => 'range', 'visible' => 0, 'items' => [], 'ranges' => array<int, array{key: string, label: string, min: ?float, max: ?float}>]`; a kulcssorrend `['stock', 'category', 'group', 'dimensions', 'size', 'brand', 'material']`

- [ ] **Step 1: Írd meg a teszteket**

A kulcsteszt várt tömbje legyen `['stock', 'category', 'group', 'dimensions', 'size', 'brand', 'material']`, az `assertSee` listája kapja meg a `'Méretek (mm)'`-t.

A fájl végére:

```php
it('filters by a dimension range, with either bound on its own', function (array $range, array $expectedSizes): void {
    Product::factory()->create(['size' => '20X47X14']);
    Product::factory()->create(['size' => '25X52X15']);
    Product::factory()->create(['size' => '30X62X16']);
    Product::factory()->create(['size' => 'A28,5']);

    $component = Livewire::test(Index::class)->set('dimensionRanges.inner_diameter', $range);

    expect($component->instance()->products->pluck('size')->sort()->values()->all())->toBe($expectedSizes);
})->with([
    'both bounds' => [['min' => '22', 'max' => '28'], ['25X52X15']],
    'only the lower bound' => [['min' => '25', 'max' => ''], ['25X52X15', '30X62X16']],
    'only the upper bound' => [['min' => null, 'max' => '25'], ['20X47X14', '25X52X15']],
]);

it('ignores invalid bounds and accepts a decimal comma', function (): void {
    Product::factory()->create(['size' => '25,4X50,8X15']);
    Product::factory()->create(['size' => '30X62X16']);

    $component = Livewire::test(Index::class)->set('dimensionRanges.inner_diameter', ['min' => 'abc', 'max' => '25,4']);
    expect($component->instance()->products->pluck('size')->all())->toBe(['25,4X50,8X15']);

    $component->set('dimensionRanges.inner_diameter', ['min' => '-5', 'max' => 'xyz']);
    expect($component->instance()->products)->toHaveCount(2);
});

it('swaps a reversed range', function (): void {
    Product::factory()->create(['size' => '25X52X15']);
    Product::factory()->create(['size' => '40X80X18']);

    $component = Livewire::test(Index::class)->set('dimensionRanges.outer_diameter', ['min' => '60', 'max' => '50']);

    expect($component->instance()->products->pluck('size')->all())->toBe(['25X52X15']);
});

it('goes back to the first page when a range changes', function (): void {
    Product::factory()->count(30)->create(['size' => '25X52X15']);

    Livewire::test(Index::class)
        ->call('gotoPage', 2)
        ->set('dimensionRanges.width.min', '10')
        ->assertSet('paginators.page', 1);
});

it('shows the range bounds of the list as placeholders', function (): void {
    Product::factory()->create(['size' => '20X47X14']);
    Product::factory()->create(['size' => '25,5X52X15']);

    $dimensions = collect(Livewire::test(Index::class)->instance()->filters)->firstWhere('key', 'dimensions');

    expect($dimensions['ranges'][0])->toBe(['key' => 'inner_diameter', 'label' => 'Belső átmérő (d)', 'min' => 20.0, 'max' => 25.5])
        ->and(Livewire::test(Index::class)->html())
        ->toContain('wire:model.live.debounce.500ms="dimensionRanges.inner_diameter.min"')
        ->toContain('placeholder="20"')
        ->toContain('placeholder="25,5"');
});

it('shows a removable chip for each range and clears the ranges with "Szűrők törlése"', function (string $component): void {
    $test = Livewire::test($component)
        ->set('dimensionRanges.inner_diameter', ['min' => '20', 'max' => '30'])
        ->set('dimensionRanges.width', ['min' => '10', 'max' => null])
        ->set('dimensionRanges.outer_diameter', ['min' => null, 'max' => '62,5']);

    expect($test->instance()->dimensionRangeChips)->toBe([
        ['key' => 'inner_diameter', 'label' => 'Belső átmérő: 20–30 mm'],
        ['key' => 'outer_diameter', 'label' => 'Külső átmérő: 62,5 mm-ig'],
        ['key' => 'width', 'label' => 'Szélesség: 10 mm-től'],
    ]);
    $test->assertSee('Belső átmérő: 20–30 mm')->assertSeeHtml('wire:click="clearDimensionRange(\'width\')"');

    $test->call('clearDimensionRange', 'width')
        ->assertSet('dimensionRanges.width', ['min' => null, 'max' => null])
        ->call('clearFilters')
        ->assertSet('dimensionRanges.inner_diameter', ['min' => null, 'max' => null]);
})->with([
    'category index' => [Index::class],
    'product list' => [ProductsIndex::class],
]);
```

- [ ] **Step 2: Futtasd, hogy elbukik**

Run: `php artisan test tests/Feature/Livewire/Products/Categories/IndexTest.php`
Expected: FAIL – `dimensionRanges` nem létező property.

- [ ] **Step 3: Trait – állapot, lekérdezés, címkék**

`app/Livewire/Concerns/FiltersProducts.php`, importok: `use Illuminate\Support\Number;`.

Konstans:

```php
    /**
     * Column => the name used in the sidebar and on the chips.
     *
     * @var array<string, array{sidebar: string, chip: string}>
     */
    private const array DIMENSIONS = [
        'inner_diameter' => ['sidebar' => 'Belső átmérő (d)', 'chip' => 'Belső átmérő'],
        'outer_diameter' => ['sidebar' => 'Külső átmérő (D)', 'chip' => 'Külső átmérő'],
        'width' => ['sidebar' => 'Szélesség (B)', 'chip' => 'Szélesség'],
    ];
```

Property a `$sizeSearch` után:

```php
    /**
     * The "from" and "to" of each dimension in mm, as the inputs send them;
     * dimensionBounds() reads them.
     *
     * @var array<string, array{min: mixed, max: mixed}>
     */
    public array $dimensionRanges = [
        'inner_diameter' => ['min' => null, 'max' => null],
        'outer_diameter' => ['min' => null, 'max' => null],
        'width' => ['min' => null, 'max' => null],
    ];
```

Metódusok az `updatedSelectedFilters()` után:

```php
    public function updatedDimensionRanges(): void
    {
        $this->resetPage();
    }

    public function clearDimensionRange(string $dimension): void
    {
        if (! array_key_exists($dimension, self::DIMENSIONS)) {
            return;
        }

        $this->dimensionRanges[$dimension] = ['min' => null, 'max' => null];
        $this->resetPage();
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    #[Computed]
    public function dimensionRangeChips(): array
    {
        $chips = [];

        foreach (self::DIMENSIONS as $column => $labels) {
            [$min, $max] = $this->dimensionBounds($column);

            $text = match (true) {
                $min !== null && $max !== null => $this->formatMillimetres($min) . '–' . $this->formatMillimetres($max) . ' mm',
                $min !== null => $this->formatMillimetres($min) . ' mm-től',
                $max !== null => $this->formatMillimetres($max) . ' mm-ig',
                default => null,
            };

            if ($text !== null) {
                $chips[] = ['key' => $column, 'label' => "{$labels['chip']}: {$text}"];
            }
        }

        return $chips;
    }
```

A `filters()`-ben a Termékcsoport elem után:

```php
            [
                'title' => 'Méretek (mm)',
                'key' => 'dimensions',
                'type' => 'range',
                'visible' => 0,
                'items' => [],
                'ranges' => $this->dimensionRangeOptions(),
            ],
```

A `filters()` docblockja:

```php
    /**
     * @return array<int, array{title: string, key: string, visible: int, items: array<int, array{name: string, value: string, count: int}>, type?: 'range', ranges?: array<int, array{key: string, label: string, min: ?float, max: ?float}>, search?: array{model: string, placeholder: string, empty: ?string}}>
     */
```

A `resetProductFilters()`-be:

```php
        $this->dimensionRanges = array_map(fn (): array => ['min' => null, 'max' => null], self::DIMENSIONS);
```

Az `applySelectedFilters()`-be a márka/anyag ciklus után:

```php
        foreach (array_keys(self::DIMENSIONS) as $column) {
            [$min, $max] = $this->dimensionBounds($column);

            if ($min !== null) {
                $query->where($column, '>=', $min);
            }

            if ($max !== null) {
                $query->where($column, '<=', $max);
            }
        }
```

Privát segédek a fájl végén:

```php
    /**
     * The smallest and largest value of each dimension in the unfiltered list.
     *
     * @return array<int, array{key: string, label: string, min: ?float, max: ?float}>
     */
    private function dimensionRangeOptions(): array
    {
        $limits = $this->filterableProducts()
            ->selectRaw(collect(array_keys(self::DIMENSIONS))
                ->map(fn (string $column): string => "min({$column}) as {$column}_min, max({$column}) as {$column}_max")
                ->implode(', '))
            ->toBase()
            ->first();

        return collect(self::DIMENSIONS)
            ->map(fn (array $labels, string $column): array => [
                'key' => $column,
                'label' => $labels['sidebar'],
                'min' => $limits?->{"{$column}_min"} === null ? null : (float) $limits->{"{$column}_min"},
                'max' => $limits?->{"{$column}_max"} === null ? null : (float) $limits->{"{$column}_max"},
            ])
            ->values()
            ->all();
    }

    /**
     * The valid bounds of a dimension, the lower one first. A bound that is
     * not a non-negative number counts as not given; a decimal comma is fine.
     *
     * @return array{0: ?float, 1: ?float}
     */
    private function dimensionBounds(string $column): array
    {
        $min = $this->millimetres($this->dimensionRanges[$column]['min'] ?? null);
        $max = $this->millimetres($this->dimensionRanges[$column]['max'] ?? null);

        if ($min !== null && $max !== null && $min > $max) {
            return [$max, $min];
        }

        return [$min, $max];
    }

    private function millimetres(mixed $value): ?float
    {
        $normalized = is_string($value) ? str_replace(',', '.', mb_trim($value)) : $value;

        if (! is_numeric($normalized) || (float) $normalized < 0) {
            return null;
        }

        return (float) $normalized;
    }

    private function formatMillimetres(float $value): string
    {
        return (string) Number::format($value, maxPrecision: 3, locale: 'hu');
    }
```

- [ ] **Step 4: A szűrősáv „range” szekciója**

`resources/views/components/categories/filter-sidebar.blade.php`: a `<div x-show="open" x-collapse class="space-y-2">` belsejét tedd egy elágazásba. Közvetlenül a nyitó div után:

```blade
                @if (($filter['type'] ?? null) === 'range')
                    @foreach ($filter['ranges'] as $range)
                        <div wire:key="range-{{ $range['key'] }}">
                            <p class="text-sm text-gray-700 mb-1">{{ $range['label'] }}</p>
                            <div class="flex items-center gap-2">
                                @foreach (['min' => 'tól', 'max' => 'ig'] as $bound => $boundLabel)
                                    @if (! $loop->first)
                                        <span class="text-gray-400">–</span>
                                    @endif
                                    <input type="number" min="0" step="any" inputmode="decimal"
                                        wire:model.live.debounce.500ms="dimensionRanges.{{ $range['key'] }}.{{ $bound }}"
                                        placeholder="{{ $range[$bound] !== null ? Number::format($range[$bound], maxPrecision: 3, locale: 'hu') : $boundLabel }}"
                                        aria-label="{{ $range['label'] }} {{ $boundLabel }}"
                                        class="w-full min-w-0 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
```

és a szekció belsejének végén, a `@if ($hiddenCount > 0) … @endif` blokk után, a záró `</div>` elé:

```blade
                @endif
```

- [ ] **Step 5: Tartomány-címkék a két oldalon**

`resources/views/livewire/products/categories/index.blade.php`:
- `$hasActiveFilters` sora: `$hasActiveFilters = collect($selectedFilters)->flatten()->isNotEmpty() || $this->dimensionRangeChips !== [];`
- a `@foreach ($selectedFilters as $key => $values) … @endforeach` külső ciklus zárása után:

```blade
                                @foreach ($this->dimensionRangeChips as $chip)
                                    <span wire:key="chip-dimension-{{ $chip['key'] }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                        {{ $chip['label'] }}
                                        <button type="button" wire:click="clearDimensionRange('{{ $chip['key'] }}')"
                                            class="hover:text-blue-600">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </span>
                                @endforeach
```

`resources/views/livewire/products/index.blade.php`: ugyanez; a `$hasActiveFilters` sora itt:
`$hasActiveFilters = collect($selectedFilters)->flatten()->isNotEmpty() || $search || $this->dimensionRangeChips !== [];`

- [ ] **Step 6: Futtasd a teszteket**

Run: `php artisan test tests/Feature/Livewire/Products tests/Feature/Livewire/ProductVisibilityTest.php tests/Feature/ProductAttributesTest.php`
Expected: PASS.

- [ ] **Step 7: Pint, teljes suite, commit**

```bash
vendor/bin/pint --dirty
php artisan test
git add app/Livewire/Concerns/FiltersProducts.php resources/views tests/Feature/Livewire/Products/Categories/IndexTest.php
git commit -m "Filter the product lists by bore, outer diameter and width ranges"
```

Expected: a teljes suite zöld (ha `ViteException`, előbb `npm run build`).

---

### Task 5: Élesítés (csak a felhasználó engedélyével)

- [ ] **Step 1: Kérj engedélyt a pushra** – a push a `main`-re azonnal élesre telepít, és migrációt futtat.
- [ ] **Step 2: Push**: `git push` (a push-hook a teljes suite-ot futtatja).
- [ ] **Step 3: Várd meg a deployt** – `ssh gordulosimering@79.172.239.215`, és ellenőrizd, hogy a `/home/gordulosimering/gordulo-simmering.hu/current/app/Services/ProductAttributeExtractor.php` létezik.
- [ ] **Step 4: Visszamenőleges feltöltés élesen**: a `current` mappában `php artisan app:extract-product-attributes`. Expected: `Frissítve: ~50 000 termék.`
- [ ] **Step 5: Ellenőrzés** – tinkerrel: `Product::webVisible()->whereNotNull('inner_diameter')->count()` ≈ 20 000, `whereNotNull('brand')` ≈ 20 000; a `https://gordulo-simmering.hu/termekkategoriak` oldalon megjelenik a négy új szűrő.
