# Kategória-import (WEB_2.0.tsv) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A `WEB_2.0.tsv` változó mélységű kategóriafáját a `product_categories` táblába importálni, és a `termekek.tsv`-ből betöltött termékeket substring-párosítással a kategórialevelekhez kötni.

**Architecture:** Egy `App\Services\CategoryImporter` szolgáltatás végzi a TSV outline-parser-t (`importTree`) és a termék-linkelést (`linkProducts`), a `CartService` mintájára. Egy vékony `app:import-categories` artisan parancs hívja, `--link` kapcsolóval. A séma előfeltétel: a meglévő (hibás) kategória-migráció javítása + új pivot migráció + a `Category` model relációinak helyretétele.

**Tech Stack:** Laravel 12, PHP 8.4, Pest v3, SQLite (teszt: `:memory:`).

## Global Constraints

- PHP: explicit return type minden metóduson; constructor property promotion; kapcsos zárójel minden vezérlési szerkezetnél.
- `final` osztályok (a projekt konvenciója szerint, ld. `CartService`, modellek).
- Eloquent, ne `DB::` — kivétel a `whereRaw` ahol elkerülhetetlen (substring fordított irány).
- Minden változás Pest-teszttel fedve; futtatás: `php artisan test --filter=<név>`.
- `vendor/bin/pint --dirty` a befejezés előtt.
- TSV: tab-elválasztás, CRLF (`\r\n`) sorvég, minden mezőt trimmelni (`\r` + whitespace).
- Idempotencia: a parancs többszöri futtatása nem duplikál.

---

### Task 1: Séma-javítások (tábla, pivot, relációk)

**Files:**
- Modify: `database/migrations/0001_01_01_000002_create_product_categories_table.php`
- Create: `database/migrations/0001_01_01_000007_create_category_product_table.php`
- Modify: `app/Models/Category.php`
- Test: `tests/Feature/CategorySchemaTest.php`

**Interfaces:**
- Produces: `product_categories` tábla (`id, name, slug unique, category_id nullable, description, display, timestamps`); `category_product` pivot (`category_id`, `product_id`, unique pár); `Category::parentCategory()`, `Category::children()`, `Category::products()` relációk.

- [ ] **Step 1: Write the failing test**

`tests/Feature/CategorySchemaTest.php`:
```php
<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;

it('builds parent-child category relations on product_categories table', function () {
    $parent = Category::create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $child = Category::create(['name' => 'Golyós', 'slug' => 'golyos', 'category_id' => $parent->id]);

    expect($child->parentCategory->id)->toBe($parent->id)
        ->and($parent->children->pluck('id')->all())->toBe([$child->id]);

    expect(\Illuminate\Support\Facades\Schema::hasTable('product_categories'))->toBeTrue();
});

it('attaches products to a category via the pivot', function () {
    $category = Category::create(['name' => 'Lev', 'slug' => 'lev']);
    $product = Product::factory()->create();

    $category->products()->attach($product->id);

    expect($category->products()->count())->toBe(1);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CategorySchemaTest`
Expected: FAIL (`no such table: product_categories` és/vagy hibás reláció).

- [ ] **Step 3: Fix the category migration table name**

`database/migrations/0001_01_01_000002_create_product_categories_table.php` — az `up()` első sorát:
```php
Schema::create('product_categories', function (Blueprint $table): void {
```
(A `categories` → `product_categories`. A `down()` már `product_categories`-t dropol, marad.)

- [ ] **Step 4: Create the pivot migration**

`database/migrations/0001_01_01_000007_create_category_product_table.php`:
```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table): void {
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['category_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
```

- [ ] **Step 5: Fix the Category model relations**

`app/Models/Category.php` — cseréld a `parentCategory()` metódust és adj hozzá `children()`-t (a `products()` marad):
```php
public function parentCategory(): BelongsTo
{
    return $this->belongsTo(self::class, 'category_id');
}

public function children(): HasMany
{
    return $this->hasMany(self::class, 'category_id');
}
```
Add hozzá az importot a fájl tetejéhez:
```php
use Illuminate\Database\Eloquent\Relations\HasMany;
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=CategorySchemaTest`
Expected: PASS (mindkét teszt zöld).

- [ ] **Step 7: Commit**

```bash
git add database/migrations app/Models/Category.php tests/Feature/CategorySchemaTest.php
git commit -m "Fix category schema: product_categories table, pivot, relations"
```

---

### Task 2: Adatfájl áthelyezése és termekek.tsv visszaállítása

**Files:**
- Rename: `WEB_2.0.tsv` → `database/data/web_kategoriak.tsv`
- Restore: `database/data/termekek.tsv`

- [ ] **Step 1: Move the category file with git**

Run:
```bash
git mv WEB_2.0.tsv database/data/web_kategoriak.tsv
```

- [ ] **Step 2: Restore the deleted products file**

Run:
```bash
git restore database/data/termekek.tsv
```

- [ ] **Step 3: Verify both files exist**

Run:
```bash
ls -l database/data/web_kategoriak.tsv database/data/termekek.tsv
```
Expected: mindkét fájl listázódik, nem üres.

- [ ] **Step 4: Commit**

```bash
git add -A database/data WEB_2.0.tsv
git commit -m "Move category TSV into database/data and restore products TSV"
```

---

### Task 3: CategoryImporter — kategóriafa parser és perzisztálás

**Files:**
- Create: `app/Services/CategoryImporter.php`
- Test: `tests/Feature/CategoryImporterTreeTest.php`

**Interfaces:**
- Produces: `CategoryImporter::importTree(string $path): int` — felépíti a fát a TSV-ből, visszaadja a létrehozott/frissített kategóriák számát. Idempotens: kategóriát `(category_id, name)` páron azonosít (`firstOrCreate`), a `slug` a teljes útvonalból, globálisan egyedire képezve.

- [ ] **Step 1: Write the failing test**

`tests/Feature/CategoryImporterTreeTest.php`:
```php
<?php

declare(strict_types=1);

use App\Models\Category;
use App\Services\CategoryImporter;

function writeTsv(string $content): string
{
    $path = tempnam(sys_get_temp_dir(), 'cat') . '.tsv';
    file_put_contents($path, $content);

    return $path;
}

it('builds a variable-depth tree with inheritance and skips blank rows', function () {
    // CRLF sorvégek; üres elválasztó sor a két ág között.
    $rows = [
        "CSAPÁGYAK\tGOLYÓS\tMÉLYHORNYÚ\tEGYSORÚ\tBECO golyóscsapágy",
        "\t\t\t\tEZO golyóscsapágy",
        "\t\t\t\t",
        "CSAPÁGYAK\tGOLYÓS\tMÉLYHORNYÚ\tKÉTSORÚ\tFAG kétsorú",
        "ZSÍRZÁSTECHNIKA\tOKS zsír",
    ];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    $importer = new CategoryImporter();
    $importer->importTree($path);

    // Főkategóriák
    $csapagyak = Category::where('name', 'CSAPÁGYAK')->whereNull('category_id')->firstOrFail();
    $zsir = Category::where('name', 'ZSÍRZÁSTECHNIKA')->whereNull('category_id')->firstOrFail();

    // Mélység és öröklődés: két levél a MÉLYHORNYÚ > EGYSORÚ alatt
    $egysoru = Category::where('name', 'EGYSORÚ')->firstOrFail();
    expect($egysoru->children->pluck('name')->sort()->values()->all())
        ->toBe(['BECO golyóscsapágy', 'EZO golyóscsapágy']);

    // Új érték a 4. oszlopban (KÉTSORÚ) nem keveredik az EGYSORÚ ággal
    $ketsoru = Category::where('name', 'KÉTSORÚ')->firstOrFail();
    expect($ketsoru->children->pluck('name')->all())->toBe(['FAG kétsorú']);

    // Depth-2 ág: ZSÍRZÁSTECHNIKA > OKS zsír
    expect(Category::where('name', 'OKS zsír')->first()->parentCategory->id)->toBe($zsir->id);

    // Üres sor nem hozott létre kategóriát
    expect(Category::whereIn('name', ['', null])->count())->toBe(0);
});

it('generates unique slugs for same-named nodes in different branches', function () {
    $rows = [
        "A\tKÖZÖS",
        "B\tKÖZÖS",
    ];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    (new CategoryImporter())->importTree($path);

    $slugs = Category::where('name', 'KÖZÖS')->pluck('slug');
    expect($slugs)->toHaveCount(2)
        ->and($slugs->unique())->toHaveCount(2);
});

it('is idempotent across repeated runs', function () {
    $rows = ["A\tB\tC"];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $first = Category::count();
    $importer->importTree($path);

    expect(Category::count())->toBe($first);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CategoryImporterTreeTest`
Expected: FAIL (`Class "App\Services\CategoryImporter" not found`).

- [ ] **Step 3: Implement the importer (tree part)**

`app/Services/CategoryImporter.php`:
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;
use RuntimeException;

final class CategoryImporter
{
    private const COLUMN_COUNT = 5;

    /**
     * Globálisan használt slug-ok, hogy ütközéskor egyedi utótagot adjunk.
     *
     * @var array<string, bool>
     */
    private array $usedSlugs = [];

    public function importTree(string $path): int
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Could not open TSV file: {$path}");
        }

        $this->usedSlugs = Category::pluck('slug')->flip()->map(fn (): bool => true)->all();

        /** @var array<int, array{model: Category, names: array<int, string>}|null> $path_nodes */
        $path_nodes = array_fill(0, self::COLUMN_COUNT, null);
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            $cells = $this->parseLine($line);

            if ($this->isBlank($cells)) {
                continue;
            }

            for ($i = 0; $i < self::COLUMN_COUNT; $i++) {
                if ($cells[$i] === '') {
                    continue;
                }

                $parentNames = [];
                $parentModel = null;
                for ($j = $i - 1; $j >= 0; $j--) {
                    if ($path_nodes[$j] !== null) {
                        $parentModel = $path_nodes[$j]['model'];
                        $parentNames = $path_nodes[$j]['names'];
                        break;
                    }
                }

                $names = [...$parentNames, $cells[$i]];
                $category = $this->upsertCategory($cells[$i], $parentModel?->id, $names);
                $count++;

                $path_nodes[$i] = ['model' => $category, 'names' => $names];
                for ($k = $i + 1; $k < self::COLUMN_COUNT; $k++) {
                    $path_nodes[$k] = null;
                }
            }
        }

        fclose($handle);

        return $count;
    }

    /**
     * @return array<int, string>
     */
    private function parseLine(string $line): array
    {
        $line = rtrim($line, "\r\n");
        $parts = explode("\t", $line);
        $cells = [];
        for ($i = 0; $i < self::COLUMN_COUNT; $i++) {
            $cells[$i] = mb_trim($parts[$i] ?? '');
        }

        return $cells;
    }

    /**
     * @param  array<int, string>  $cells
     */
    private function isBlank(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string>  $names
     */
    private function upsertCategory(string $name, ?int $parentId, array $names): Category
    {
        $existing = Category::where('name', $name)
            ->where('category_id', $parentId)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return Category::create([
            'name' => $name,
            'category_id' => $parentId,
            'slug' => $this->uniqueSlug($names),
        ]);
    }

    /**
     * @param  array<int, string>  $names
     */
    private function uniqueSlug(array $names): string
    {
        $base = Str::slug(implode('-', $names)) ?: 'kategoria';
        $slug = $base;
        $n = 1;
        while (isset($this->usedSlugs[$slug])) {
            $n++;
            $slug = $base . '-' . $n;
        }
        $this->usedSlugs[$slug] = true;

        return $slug;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=CategoryImporterTreeTest`
Expected: PASS (mindhárom teszt zöld).

- [ ] **Step 5: Commit**

```bash
git add app/Services/CategoryImporter.php tests/Feature/CategoryImporterTreeTest.php
git commit -m "Add CategoryImporter tree parser"
```

---

### Task 4: CategoryImporter — termék-összekötés (substring)

**Files:**
- Modify: `app/Services/CategoryImporter.php`
- Test: `tests/Feature/CategoryImporterLinkTest.php`

**Interfaces:**
- Consumes: `Category::children()`, `Category::products()`, `Product` model (`name`).
- Produces: `CategoryImporter::linkProducts(): int` — minden levélkategóriához (gyermek nélküli) hozzáköti azokat a termékeket, ahol kis-/nagybetű-érzéketlenül a `products.name` tartalmazza a levél nevét, VAGY a levél neve tartalmazza a terméknevet. `syncWithoutDetaching`-gel. Visszaadja a létrehozott kapcsolatok számát.

- [ ] **Step 1: Write the failing test**

`tests/Feature/CategoryImporterLinkTest.php`:
```php
<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryImporter;

it('links products to leaf categories by substring, both directions', function () {
    $parent = Category::create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $leaf = Category::create([
        'name' => 'egysorú mélyhornyú golyóscsapágy',
        'slug' => 'egysoru-melyhornyu',
        'category_id' => $parent->id,
    ]);
    $exactLeaf = Category::create([
        'name' => 'DURACELL 9V-os elem',
        'slug' => 'duracell-9v',
        'category_id' => $parent->id,
    ]);

    // A: terméknév TARTALMAZZA a levél nevét
    $match = Product::factory()->create(['name' => 'FAG egysorú mélyhornyú golyóscsapágy 6203']);
    // Nem illeszkedő
    $noMatch = Product::factory()->create(['name' => 'OKS kenőzsír 200ml']);
    // B: a levél neve TARTALMAZZA a terméknevet (rövidebb terméknév)
    $reverse = Product::factory()->create(['name' => 'DURACELL 9V-os elem']);

    $linked = (new CategoryImporter())->linkProducts();

    expect($leaf->products()->pluck('products.id')->all())->toBe([$match->id])
        ->and($exactLeaf->products()->pluck('products.id')->all())->toBe([$reverse->id])
        ->and($noMatch->categories ?? collect())->toHaveCount(0)
        ->and($linked)->toBeGreaterThan(0);
});

it('does not link products to non-leaf (parent) categories', function () {
    $parent = Category::create(['name' => 'csapágy', 'slug' => 'csapagy']);
    Category::create(['name' => 'csapágy egysoros', 'slug' => 'csapagy-egysoros', 'category_id' => $parent->id]);
    Product::factory()->create(['name' => 'valami csapágy egysoros termék']);

    (new CategoryImporter())->linkProducts();

    expect($parent->products()->count())->toBe(0);
});

it('link step is idempotent', function () {
    $leaf = Category::create(['name' => 'egyedi termék', 'slug' => 'egyedi-termek']);
    Product::factory()->create(['name' => 'egyedi termék']);

    $importer = new CategoryImporter();
    $importer->linkProducts();
    $first = $leaf->products()->count();
    $importer->linkProducts();

    expect($leaf->products()->count())->toBe($first);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CategoryImporterLinkTest`
Expected: FAIL (`Call to undefined method ...linkProducts()`).

- [ ] **Step 3: Add a categories() relation to Product**

`app/Models/Product.php` — add hozzá a relációt (és a `BelongsToMany` importot a fájl tetejéhez):
```php
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
```
A metódus a model törzsébe:
```php
public function categories(): BelongsToMany
{
    return $this->belongsToMany(Category::class);
}
```

- [ ] **Step 4: Implement linkProducts()**

`app/Services/CategoryImporter.php` — add hozzá a metódust az osztályhoz:
```php
public function linkProducts(): int
{
    $links = 0;

    Category::query()
        ->whereDoesntHave('children')
        ->whereNotNull('name')
        ->chunkById(200, function ($leaves) use (&$links): void {
            foreach ($leaves as $leaf) {
                $name = (string) $leaf->name;
                if ($name === '') {
                    continue;
                }

                $escaped = addcslashes($name, '%_\\');

                $productIds = \App\Models\Product::query()
                    ->where(function ($query) use ($escaped, $name): void {
                        $query->where('name', 'like', '%' . $escaped . '%', )
                            ->orWhereRaw("? like '%' || name || '%'", [$name]);
                    })
                    ->whereNotNull('name')
                    ->pluck('id')
                    ->all();

                if ($productIds === []) {
                    continue;
                }

                $changes = $leaf->products()->syncWithoutDetaching($productIds);
                $links += count($changes['attached']);
            }
        });

    return $links;
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=CategoryImporterLinkTest`
Expected: PASS (mindhárom teszt zöld).

- [ ] **Step 6: Commit**

```bash
git add app/Services/CategoryImporter.php app/Models/Product.php tests/Feature/CategoryImporterLinkTest.php
git commit -m "Add product-to-category substring linking"
```

---

### Task 5: Artisan parancs — app:import-categories

**Files:**
- Create: `app/Console/Commands/ImportCategories.php`
- Test: `tests/Feature/ImportCategoriesCommandTest.php`

**Interfaces:**
- Consumes: `CategoryImporter::importTree()`, `CategoryImporter::linkProducts()`.
- Produces: `app:import-categories` parancs `--link` opcióval; a fát a `database/data/web_kategoriak.tsv`-ből importálja.

- [ ] **Step 1: Write the failing test**

`tests/Feature/ImportCategoriesCommandTest.php`:
```php
<?php

declare(strict_types=1);

use App\Models\Category;

it('imports the real category tree from database/data', function () {
    $this->artisan('app:import-categories')
        ->assertSuccessful();

    // 16 főkategória a fájlban
    expect(Category::whereNull('category_id')->count())->toBe(16)
        ->and(Category::where('name', 'CSAPÁGYAK')->whereNull('category_id')->exists())->toBeTrue();
});

it('also links products when --link is passed', function () {
    \App\Models\Product::factory()->create(['name' => 'DURACELL 9V-os elem']);

    $this->artisan('app:import-categories', ['--link' => true])
        ->assertSuccessful();

    expect(Category::has('products')->count())->toBeGreaterThan(0);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ImportCategoriesCommandTest`
Expected: FAIL (a parancs nem létezik).

- [ ] **Step 3: Create the command**

`app/Console/Commands/ImportCategories.php`:
```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CategoryImporter;
use Illuminate\Console\Command;

final class ImportCategories extends Command
{
    protected $signature = 'app:import-categories {--link : Termékek hozzákötése a kategórialevelekhez}';

    protected $description = 'Kategóriafa importálása a web_kategoriak.tsv-ből, opcionális termék-összekötéssel';

    public function handle(CategoryImporter $importer): int
    {
        $path = database_path('data/web_kategoriak.tsv');

        if (! file_exists($path)) {
            $this->error("TSV file not found: {$path}");

            return self::FAILURE;
        }

        $this->info('Kategóriafa importálása...');
        $count = $importer->importTree($path);
        $this->info("Kész: {$count} kategória feldolgozva.");

        if ($this->option('link')) {
            $this->info('Termékek összekötése a kategóriákkal...');
            $links = $importer->linkProducts();
            $this->info("Kész: {$links} termék-kapcsolat létrehozva.");
        }

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ImportCategoriesCommandTest`
Expected: PASS (mindkét teszt zöld).

- [ ] **Step 5: Run the full new test group and Pint**

Run:
```bash
php artisan test --filter='Category|ImportCategories'
vendor/bin/pint --dirty
```
Expected: minden teszt zöld; a Pint formázza a módosított fájlokat.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/ImportCategories.php tests/Feature/ImportCategoriesCommandTest.php
git commit -m "Add app:import-categories command"
```

---

## Self-Review

**Spec coverage:**
- Séma-javítások (spec 1) → Task 1.
- Adatfájl áthelyezése + termekek.tsv visszaállítása (spec 2) → Task 2.
- Kategóriafa felépítése, outline-parser, öröklődés, üres sorok, slug, idempotencia, márka-ág kategóriaként (spec 3) → Task 3.
- Termék-összekötés substring, csak levélhez, kétirányú, `LIKE`, `syncWithoutDetaching` (spec 4) → Task 4.
- Artisan parancs `--link`-kel (spec 5) → Task 5.
- Tesztelés (spec 6) → minden taskban TDD.
- Nyitott pont: FORGALMAZOTT MÁRKÁINK termék-linkelés a `supplier` alapján — szándékosan kihagyva (a levél-substring természetesen kevés/0 terméket köt rá), a spec jövőbeli munkaként jelöli.

**Megjegyzés a teszt-adatra:** a Task 5 első tesztje a valódi `database/data/web_kategoriak.tsv`-re támaszkodik (16 főkategória). Ez a fájl a repóban van (Task 2 után), így a teszt determinisztikus.

**Placeholder scan:** nincs TBD/TODO; minden lépés tartalmazza a tényleges kódot és a futtatandó parancsot.

**Type consistency:** `importTree(string): int`, `linkProducts(): int`, `Category::children()/parentCategory()/products()`, `Product::categories()` — végig konzisztens a taskok között.
