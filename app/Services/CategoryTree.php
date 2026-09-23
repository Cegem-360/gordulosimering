<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Support\Collection;

/**
 * Read-side view of the category tree for the storefront: which categories
 * have anything to show, and what lies beneath them. Scoped to the request,
 * so the sidebar, the top menu and the category page share two queries.
 */
#[Scoped]
final class CategoryTree
{
    /** @var array<int, array<int, int>>|null */
    private ?array $childIdsByParent = null;

    /** @var array<int, true>|null */
    private ?array $stockedIds = null;

    /**
     * The category id plus every descendant id.
     *
     * @return array<int, int>
     */
    public function descendantIds(Category $category): array
    {
        $childIdsByParent = $this->childIdsByParent();
        $ids = [$category->id];
        $stack = [$category->id];

        while ($stack !== []) {
            foreach ($childIdsByParent[array_pop($stack)] ?? [] as $childId) {
                $ids[] = $childId;
                $stack[] = $childId;
            }
        }

        return $ids;
    }

    /**
     * Whether the category or any of its descendants holds a web-visible
     * product. Empty categories stay out of the menus and the tiles.
     */
    public function isStocked(Category $category): bool
    {
        return isset($this->stockedIds()[$category->id]);
    }

    /**
     * @template TCollection of Collection<int, Category>
     *
     * @param  TCollection  $categories
     * @return TCollection
     */
    public function stocked(Collection $categories): Collection
    {
        return $categories->filter($this->isStocked(...))->values();
    }

    /**
     * The uploaded category photo, or else the picture of the first
     * web-visible product beneath it that has one.
     */
    public function coverImageUrl(Category $category): ?string
    {
        if ($category->image_url !== null) {
            return $category->image_url;
        }

        return Product::query()
            ->webVisible()
            ->whereNotNull('featured_image')
            ->whereHas('categories', fn ($query) => $query->whereIn('product_categories.id', $this->descendantIds($category)))
            ->orderBy('name')
            ->first(['id', 'featured_image', 'images'])
            ?->image_url;
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function childIdsByParent(): array
    {
        return $this->childIdsByParent ??= Category::query()
            ->whereNotNull('category_id')
            ->get(['id', 'category_id'])
            ->groupBy('category_id')
            ->map(fn (Collection $children): array => $children->pluck('id')->all())
            ->all();
    }

    /**
     * @return array<int, true>
     */
    private function stockedIds(): array
    {
        if ($this->stockedIds !== null) {
            return $this->stockedIds;
        }

        $parentById = Category::query()->pluck('category_id', 'id')->all();
        $stocked = [];

        $directlyStocked = Category::query()
            ->whereHas('products', fn ($query) => $query->webVisible())
            ->pluck('id');

        foreach ($directlyStocked as $id) {
            while ($id !== null && ! isset($stocked[$id])) {
                $stocked[$id] = true;
                $id = $parentById[$id] ?? null;
            }
        }

        return $this->stockedIds = $stocked;
    }
}
