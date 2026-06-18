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
