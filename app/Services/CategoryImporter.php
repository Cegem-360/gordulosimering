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
    private const string BRAND_ROOT_NAME = 'FORGALMAZOTT MÁRKÁINK';

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
     * A file leveleiből (a legmélyebb, gyermek nélküli cellák = márka termék-sorok)
     * gyűjtött illesztési szabályok. Az importTree tölti fel, a linkProducts használja.
     * Hossz szerint csökkenően rendezve, hogy a legspecifikusabb illeszkedés nyerjen.
     *
     * @var array<int, array{norm: string, len: int, category_id: int}>
     */
    private array $productLines = [];

    /**
     * A kategóriafát KIZÁRÓLAG a belső csomópontokból építi fel (amelyeknek van
     * gyermekük). A soronkénti legmélyebb cella (levél) nem kategória, hanem egy
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
     * illeszkedő EGY termék-sor szülő-kategóriájához köt. A párosítás normalizált
     * (kisbetű + ékezet nélküli) substringre épül, determinisztikusan PHP-ben.
     */
    public function linkProducts(): int
    {
        if ($this->productLines === []) {
            return 0;
        }

        $lines = $this->productLines;
        $links = 0;

        Product::query()
            ->whereNotNull('name')
            ->select(['id', 'name'])
            ->chunkById(1000, function ($products) use ($lines, &$links): void {
                /** @var array<int, array<int, int>> $byCategory */
                $byCategory = [];

                foreach ($products as $product) {
                    $normalized = $this->normalize((string) $product->name);
                    if ($normalized === '') {
                        continue;
                    }

                    foreach ($lines as $line) {
                        if (str_contains($normalized, $line['norm'])) {
                            $byCategory[$line['category_id']][] = $product->id;

                            break;
                        }
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

                if (! isset($nodes[$key])) {
                    $nodes[$key] = ['name' => $cells[$i], 'names' => $names, 'parentKey' => $parentKey, 'order' => $order++];
                }

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
     * Létrehozza a belső csomópontokat kategóriaként (szülők előbb), a leveleket
     * pedig termék-sorként gyűjti a linkeléshez.
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

            if ($hasChildren[$key] ?? false) {
                $category = $this->upsertCategory($node['name'], $parentId, $node['names']);
                $idByKey[$key] = $category->id;
                $count++;

                continue;
            }

            $this->collectProductLine($node, $parentId);
        }

        usort($this->productLines, fn (array $a, array $b): int => $b['len'] <=> $a['len']);

        return $count;
    }

    /**
     * @param  array{name: string, names: array<int, string>, parentKey: ?string, order: int}  $node
     */
    private function collectProductLine(array $node, ?int $parentId): void
    {
        if ($parentId === null) {
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

        $this->productLines[] = ['norm' => $norm, 'len' => mb_strlen($node['name']), 'category_id' => $parentId];
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
        $existing = Category::query()->where('name', $name)
            ->where('category_id', $parentId)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return Category::query()->create([
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
