<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use RuntimeException;

final class CategoryImporter
{
    private const int COLUMN_COUNT = 5;

    /**
     * A márkalistát tartalmazó főkategória neve. Ennek gyermekeit (puszta
     * márkanevek, pl. "SKF") kihagyjuk a termék-linkelésből, mert substringként
     * a terméknevek tömegére illenek és szétkenik a besorolást.
     */
    private const string BRAND_ROOT_NAME = Category::BRAND_ROOT_NAME;

    /**
     * Ennél rövidebb termék-sor nevet nem linkelünk (túl generikus, túl-illeszt).
     */
    private const int MIN_LINE_NAME_LENGTH = 4;

    private const string PATH_SEPARATOR = "\x1f";

    /**
     * Globálisan használt slug-ok, hogy ütközéskor egyedi utótagot adjunk.
     *
     * @var array<string, bool>
     */
    private array $usedSlugs = [];

    /**
     * A file termék-soraiból (a kisbetűs levélcellák, pl. "SKF csapágy")
     * gyűjtött illesztési szabályok. Az importTree tölti fel, a linkProducts használja.
     * Hossz szerint csökkenően rendezve, hogy a legspecifikusabb illeszkedés nyerjen.
     *
     * @var array<int, array{norm: string, len: int, category_id: int}>
     */
    private array $productLines = [];

    /**
     * A kategóriafát a file NAGYBETŰS celláiból és a gyermekkel rendelkező
     * csomópontokból építi fel. Az ügyfél táblázatában a nagybetűs cella a
     * típus (pl. "GOLYÓS CSAPÁGY", "HATLAPFEJŰ CSAVAR"), akkor is, ha nincs
     * alatta sor; a kisbetűs levél ("SKF csapágy", "NORMA benzincsőbilincs")
     * márka termék-sor, amely alapján a valós termékek a szülő kategóriához
     * kötődnek – lásd linkProducts().
     */
    public function importTree(string $path): int
    {
        $rows = $this->readRows($path);
        [$nodes, $hasChildren] = $this->buildNodes($rows);

        return $this->persist($nodes, $hasChildren);
    }

    /**
     * Minden terméket a hozzá legjobban (leghosszabb, legspecifikusabb névvel)
     * illeszkedő termék-sor kategóriájához köt. Ha több, ugyanolyan hosszú sor
     * illeszkedik, mindegyik kategóriához: az ügyfél táblázata szándékosan több
     * helyen is felsorol egy termékcsaládot (pl. a NORMA bilincsek a BILINCSEK
     * és a KÖTŐELEMEK alatt is). A párosítás normalizált (kisbetű + ékezet
     * nélküli) substringre épül, determinisztikusan PHP-ben.
     *
     * A termék kikerül az új kategóriája őseiből: ha egy korábbi import még a
     * szülőhöz kötötte, mert az alkategória akkor nem létezett, ez a kötés
     * felesleges, hiszen a szülő oldala a leszármazottak termékeit is mutatja.
     * A kézzel, más ágba tett besorolás megmarad.
     */
    public function linkProducts(): int
    {
        if ($this->productLines === []) {
            return 0;
        }

        $lines = $this->productLines;
        $ancestorIds = $this->ancestorIdsByCategory();
        $links = 0;

        Product::query()
            ->whereNotNull('name')
            ->select(['id', 'name'])
            ->chunkById(1000, function ($products) use ($lines, $ancestorIds, &$links): void {
                /** @var array<int, array<int, int>> $byCategory */
                $byCategory = [];
                /** @var array<int, array<int, int>> $staleByAncestor */
                $staleByAncestor = [];

                foreach ($products as $product) {
                    $matched = $this->bestMatchingCategoryIds((string) $product->name, $lines);
                    if ($matched === []) {
                        continue;
                    }

                    $ancestors = array_unique(array_merge(...array_map(fn (int $id): array => $ancestorIds[$id] ?? [], $matched)));

                    foreach (array_diff($matched, $ancestors) as $categoryId) {
                        $byCategory[$categoryId][] = $product->id;
                    }

                    foreach ($ancestors as $ancestorId) {
                        $staleByAncestor[$ancestorId][] = $product->id;
                    }
                }

                foreach ($byCategory as $categoryId => $productIds) {
                    $changes = Category::query()
                        ->whereKey($categoryId)
                        ->first()
                        ->products()
                        ->syncWithoutDetaching($productIds);
                    $links += count($changes['attached']);
                }

                foreach ($staleByAncestor as $ancestorId => $productIds) {
                    Category::query()->whereKey($ancestorId)->first()?->products()->detach($productIds);
                }
            });

        return $links;
    }

    /**
     * A FORGALMAZOTT MÁRKÁINK alatti márka-kategóriákhoz köti az összes
     * terméket, amelynek nevében a márka önálló szóként szerepel ("SKF …",
     * "Tokés (FAG) csapágy"), a termékkategóriától függetlenül. Szóhatárra
     * illeszt, hogy az "INA" ne találja meg a "…lamina…" szót.
     */
    public function linkBrands(): int
    {
        $brandRoot = Category::query()
            ->whereNull('category_id')
            ->where('name', self::BRAND_ROOT_NAME)
            ->first();

        if ($brandRoot === null) {
            return 0;
        }

        $brands = $brandRoot->children()->get(['id', 'name'])
            ->map(fn (Category $brand): array => [
                'id' => $brand->id,
                'pattern' => '/(?<![a-z0-9])' . preg_quote($this->normalize($brand->name), '/') . '(?![a-z0-9])/',
            ])
            ->all();

        $links = 0;

        Product::query()
            ->whereNotNull('name')
            ->select(['id', 'name'])
            ->chunkById(1000, function ($products) use ($brands, &$links): void {
                /** @var array<int, array<int, int>> $byBrand */
                $byBrand = [];

                foreach ($products as $product) {
                    $normalized = $this->normalize((string) $product->name);

                    foreach ($brands as $brand) {
                        if (preg_match($brand['pattern'], $normalized) === 1) {
                            $byBrand[$brand['id']][] = $product->id;
                        }
                    }
                }

                foreach ($byBrand as $brandId => $productIds) {
                    $changes = Category::query()->whereKey($brandId)->first()->products()->syncWithoutDetaching($productIds);
                    $links += count($changes['attached']);
                }
            });

        return $links;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function readRows(string $path): array
    {
        $handle = fopen($path, 'r');
        throw_if($handle === false, RuntimeException::class, 'Could not open TSV file: ' . $path);

        $rows = [];
        while (($line = fgets($handle)) !== false) {
            $cells = $this->parseLine($line);
            if (! $this->isBlank($cells)) {
                $rows[] = $cells;
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * A file-t fa-csomópontokká alakítja: minden cellához kiszámolja a teljes
     * elérési útját és a szülőjét, és jelöli, mely csomópontoknak van gyermekük.
     *
     * @param  array<int, array<int, string>>  $rows
     * @return array{0: array<string, array{name: string, names: array<int, string>, parentKey: ?string, order: int}>, 1: array<string, bool>}
     */
    private function buildNodes(array $rows): array
    {
        /** @var array<int, array<int, string>|null> $pathNames */
        $pathNames = array_fill(0, self::COLUMN_COUNT, null);
        $nodes = [];
        $hasChildren = [];
        $order = 0;

        foreach ($rows as $cells) {
            for ($i = 0; $i < self::COLUMN_COUNT; $i++) {
                if ($cells[$i] === '') {
                    continue;
                }

                $parentNames = null;
                for ($j = $i - 1; $j >= 0; $j--) {
                    if ($pathNames[$j] !== null) {
                        $parentNames = $pathNames[$j];

                        break;
                    }
                }

                $names = $parentNames === null ? [$cells[$i]] : [...$parentNames, $cells[$i]];
                $key = implode(self::PATH_SEPARATOR, $names);
                $parentKey = $parentNames === null ? null : implode(self::PATH_SEPARATOR, $parentNames);

                $nodes[$key] ??= ['name' => $cells[$i], 'names' => $names, 'parentKey' => $parentKey, 'order' => $order++];

                if ($parentKey !== null) {
                    $hasChildren[$parentKey] = true;
                }

                $pathNames[$i] = $names;
                for ($k = $i + 1; $k < self::COLUMN_COUNT; $k++) {
                    $pathNames[$k] = null;
                }
            }
        }

        return [$nodes, $hasChildren];
    }

    /**
     * Létrehozza a kategóriákat (szülők előbb): a belső csomópontokat és a
     * nagybetűs leveleket. A kisbetűs leveleket termék-sorként gyűjti a linkeléshez.
     *
     * @param  array<string, array{name: string, names: array<int, string>, parentKey: ?string, order: int}>  $nodes
     * @param  array<string, bool>  $hasChildren
     */
    private function persist(array $nodes, array $hasChildren): int
    {
        $this->usedSlugs = Category::query()->pluck('slug')->flip()->map(fn (): bool => true)->all();
        $this->productLines = [];

        uasort($nodes, fn (array $a, array $b): int => count($a['names']) <=> count($b['names']) ?: $a['order'] <=> $b['order']);

        /** @var array<string, int> $idByKey */
        $idByKey = [];
        $count = 0;

        foreach ($nodes as $key => $node) {
            $parentId = $node['parentKey'] !== null ? ($idByKey[$node['parentKey']] ?? null) : null;

            $isLeaf = ! ($hasChildren[$key] ?? false);

            if (! $isLeaf || $this->isCategoryName($node['name'])) {
                $category = $this->upsertCategory($node['name'], $parentId, $node['names'], $node['order']);
                $idByKey[$key] = $category->id;
                $count++;

                if ($isLeaf && str_contains($node['name'], ' ')) {
                    $this->collectProductLine($node, $category->id);
                }

                continue;
            }

            $this->collectProductLine($node, $parentId);
        }

        usort($this->productLines, fn (array $a, array $b): int => $b['len'] <=> $a['len']);

        return $count;
    }

    /**
     * Egy termék-sort a kategóriájához rendel. A kisbetűs levél a szülőjéhez
     * tartozik; a sor nélküli, több szavas nagybetűs levél-kategória (pl. "SKF
     * VASÚTI ÁGYTOKCSAPÁGY") a saját nevével illeszt, hogy a termékei ne
     * maradjanak kategória nélkül. Az egyszavas típusnevek ("NORMÁL", "RUGÓS")
     * túl sok terméknévben előfordulnak ehhez.
     *
     * @param  array{name: string, names: array<int, string>, parentKey: ?string, order: int}  $node
     */
    private function collectProductLine(array $node, ?int $categoryId): void
    {
        if ($categoryId === null) {
            return;
        }

        if ($node['parentKey'] === self::BRAND_ROOT_NAME) {
            return;
        }

        if (mb_strlen($node['name']) < self::MIN_LINE_NAME_LENGTH) {
            return;
        }

        $norm = $this->normalize($node['name']);
        if ($norm === '') {
            return;
        }

        $this->productLines[] = ['norm' => $norm, 'len' => mb_strlen($node['name']), 'category_id' => $categoryId];
    }

    /**
     * A leghosszabb illeszkedő termék-sor(ok) kategóriái. A sorok hossz szerint
     * csökkenően rendezettek, így az első rövidebb sornál meg lehet állni.
     *
     * @param  array<int, array{norm: string, len: int, category_id: int}>  $lines
     * @return array<int, int>
     */
    private function bestMatchingCategoryIds(string $name, array $lines): array
    {
        $normalized = $this->normalize($name);
        if ($normalized === '') {
            return [];
        }

        $bestLength = null;
        $categoryIds = [];

        foreach ($lines as $line) {
            if ($bestLength !== null && $line['len'] < $bestLength) {
                break;
            }

            if (str_contains($normalized, $line['norm'])) {
                $bestLength = $line['len'];
                $categoryIds[$line['category_id']] = $line['category_id'];
            }
        }

        return array_values($categoryIds);
    }

    /**
     * Kategória-e a cella: a nagybetűs, betűt tartalmazó név típus, a
     * kisbetűs márka termék-sor.
     */
    private function isCategoryName(string $name): bool
    {
        return preg_match('/\p{Lu}/u', $name) === 1 && mb_strtoupper($name) === $name;
    }

    /**
     * Minden kategória összes őse, a közvetlen szülőtől felfelé.
     *
     * @return array<int, array<int, int>>
     */
    private function ancestorIdsByCategory(): array
    {
        $parentById = Category::query()->pluck('category_id', 'id')->all();
        $ancestors = [];

        foreach (array_keys($parentById) as $id) {
            $chain = [];
            $parent = $parentById[$id];
            while ($parent !== null && ! in_array($parent, $chain, true)) {
                $chain[] = $parent;
                $parent = $parentById[$parent] ?? null;
            }

            $ancestors[$id] = $chain;
        }

        return $ancestors;
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii($value));
    }

    /**
     * @return array<int, string>
     */
    private function parseLine(string $line): array
    {
        $line = mb_rtrim($line, "\r\n");
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
        return array_all($cells, fn ($cell): bool => $cell === '');
    }

    /**
     * A sorrend a file-beli helyből jön, de csak ha még nincs: az adminban
     * átrendezett kategóriák sorrendjét az újabb import nem írja felül.
     *
     * @param  array<int, string>  $names
     */
    private function upsertCategory(string $name, ?int $parentId, array $names, int $order): Category
    {
        $existing = Category::query()->where('name', $name)
            ->where('category_id', $parentId)
            ->first();

        if ($existing !== null) {
            if ($existing->sort_order === null) {
                $existing->update(['sort_order' => $order]);
            }

            return $existing;
        }

        return Category::query()->create([
            'name' => $name,
            'category_id' => $parentId,
            'slug' => $this->uniqueSlug($names),
            'sort_order' => $order,
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
