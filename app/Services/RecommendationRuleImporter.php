<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\RecommendationRule;
use Illuminate\Support\Collection;

/**
 * Reads the client's "gyakran veszik együtt" sheet (2026-10-14) into
 * recommendation rules. Each row: what is bought in columns A–C (category
 * path from a main category; MINDENHEZ = everything), what is offered in
 * columns D–F (category path, or a product code prefix such as TMAS when D is
 * no main category). The first row is the heading. Rules already present are
 * skipped, so a sheet can be imported again after additions.
 */
final class RecommendationRuleImporter
{
    private const string EVERYTHING = 'MINDENHEZ';

    /** @var Collection<int, Category>|null */
    private ?Collection $categories = null;

    public function __construct(private readonly XlsxReader $reader) {}

    /**
     * @return array{created: int, skipped: int, unresolved: array<int, string>}
     */
    public function import(string $path): array
    {
        return $this->importRows(array_slice($this->reader->rows($path), 1));
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array{created: int, skipped: int, unresolved: array<int, string>}
     */
    public function importRows(array $rows): array
    {
        $created = 0;
        $skipped = 0;
        $unresolved = [];

        foreach ($rows as $row) {
            $row = array_pad(array_map(mb_trim(...), $row), 6, '');
            $source = array_values(array_filter(array_slice($row, 0, 3)));
            $target = array_values(array_filter(array_slice($row, 3, 3)));

            if ($source === [] || $target === []) {
                continue;
            }

            $attributes = $this->attributes($source, $target);

            if ($attributes === null) {
                $unresolved[] = implode(' / ', $source) . ' → ' . implode(' / ', $target);

                continue;
            }

            $rule = RecommendationRule::query()->firstOrCreate($attributes);
            $rule->wasRecentlyCreated ? $created++ : $skipped++;
        }

        return ['created' => $created, 'skipped' => $skipped, 'unresolved' => $unresolved];
    }

    /**
     * @param  array<int, string>  $source
     * @param  array<int, string>  $target
     * @return array{source_category_id: ?int, target_category_id: ?int, target_code_prefix: ?string}|null
     */
    private function attributes(array $source, array $target): ?array
    {
        $sourceCategory = null;

        if (mb_strtoupper($source[0]) !== self::EVERYTHING) {
            $sourceCategory = $this->categoryAt($source);

            if (! $sourceCategory instanceof Category) {
                return null;
            }
        }

        $targetCategory = $this->categoryAt($target);

        if ($targetCategory instanceof Category) {
            return ['source_category_id' => $sourceCategory?->id, 'target_category_id' => $targetCategory->id, 'target_code_prefix' => null];
        }

        if (count($target) === 1) {
            return ['source_category_id' => $sourceCategory?->id, 'target_category_id' => null, 'target_code_prefix' => $target[0]];
        }

        return null;
    }

    /**
     * The category at the end of a name path from a main category.
     *
     * @param  array<int, string>  $names
     */
    private function categoryAt(array $names): ?Category
    {
        $this->categories ??= Category::query()->get(['id', 'name', 'category_id']);
        $parentId = null;
        $category = null;

        foreach ($names as $name) {
            $category = $this->categories->first(fn (Category $candidate): bool => $candidate->category_id === $parentId
                && mb_strtoupper(mb_trim($candidate->name)) === mb_strtoupper($name));

            if (! $category instanceof Category) {
                return null;
            }

            $parentId = $category->id;
        }

        return $category;
    }
}
