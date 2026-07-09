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
     * A márkalistát tartalmazó főkategória neve. Ennek leveleit (puszta
     * márkanevek, pl. "SKF") kihagyjuk a termék-linkelésből, mert substringként
     * a terméknevek tömegére illenek és szétkenik a besorolást.
     */
    private const string BRAND_ROOT_NAME = 'FORGALMAZOTT MÁRKÁINK';

    /**
     * Ennél rövidebb levélnevet nem linkelünk (túl generikus, túl-illeszt).
     */
    private const int MIN_LEAF_NAME_LENGTH = 4;

    /**
     * Globálisan használt slug-ok, hogy ütközéskor egyedi utótagot adjunk.
     *
     * @var array<string, bool>
     */
    private array $usedSlugs = [];

    /**
     * Minden terméket a hozzá legjobban (leghosszabb, legspecifikusabb névvel)
     * illeszkedő EGY levélkategóriához köt. A párosítás normalizált (kisbetű +
     * ékezet nélküli) substringre épül, determinisztikusan PHP-ben — nem a DB
     * kollációjára bízva, ami ékezet-érzéketlenül túl-illesztene.
     */
    public function linkProducts(): int
    {
        $leaves = $this->candidateLeaves();
        if ($leaves === []) {
            return 0;
        }

        $links = 0;

        Product::query()
            ->whereNotNull('name')
            ->select(['id', 'name'])
            ->chunkById(1000, function ($products) use ($leaves, &$links): void {
                /** @var array<int, array<int, int>> $byCategory */
                $byCategory = [];

                foreach ($products as $product) {
                    $normalized = $this->normalize((string) $product->name);
                    if ($normalized === '') {
                        continue;
                    }

                    foreach ($leaves as $leaf) {
                        if (str_contains($normalized, $leaf['norm'])) {
                            $byCategory[$leaf['id']][] = $product->id;
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

    public function importTree(string $path): int
    {
        $handle = fopen($path, 'r');
        throw_if($handle === false, RuntimeException::class, 'Could not open TSV file: ' . $path);

        $this->usedSlugs = Category::query()->pluck('slug')->flip()->map(fn (): bool => true)->all();

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
     * A linkelhető levélkategóriák normalizált neveik szerint, hosszúság szerint
     * csökkenően rendezve (így a leghosszabb – legspecifikusabb – illeszkedés nyer).
     * A márka-ág és a túl rövid nevek kimaradnak.
     *
     * @return array<int, array{id: int, norm: string}>
     */
    private function candidateLeaves(): array
    {
        $brandLeafIds = $this->brandLeafIds();

        $leaves = Category::query()
            ->whereDoesntHave('children')
            ->whereNotNull('name')
            ->whereNotIn('id', $brandLeafIds)
            ->get(['id', 'name'])
            ->filter(fn (Category $leaf): bool => mb_strlen((string) $leaf->name) >= self::MIN_LEAF_NAME_LENGTH)
            ->map(fn (Category $leaf): array => [
                'id' => $leaf->id,
                'norm' => $this->normalize((string) $leaf->name),
                'len' => mb_strlen((string) $leaf->name),
            ])
            ->filter(fn (array $leaf): bool => $leaf['norm'] !== '')
            ->sortByDesc('len')
            ->values()
            ->all();

        return array_map(fn (array $leaf): array => ['id' => $leaf['id'], 'norm' => $leaf['norm']], $leaves);
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii($value));
    }

    /**
     * A márka-főkategória közvetlen gyermekeinek (a márka-leveleknek) az id-jai.
     * A márka-ág sekély (gyökér → márkanevek levélként), ezért a közvetlen
     * gyermekek lefedik a teljes ágat.
     *
     * @return array<int, int>
     */
    private function brandLeafIds(): array
    {
        $brandRoot = Category::query()
            ->whereNull('category_id')
            ->where('name', self::BRAND_ROOT_NAME)
            ->first();

        if ($brandRoot === null) {
            return [];
        }

        return $brandRoot->children()->pluck('id')->all();
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
