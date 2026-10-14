<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\RecommendationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The "Gyakran együtt vásárolják" products for a product page, from the
 * admin's recommendation rules. Every rule matching the product's categories
 * (or its parents) contributes in turn, in-stock products first. The pick is
 * random and kept for the calendar week, so the row changes weekly, as the
 * client asked (2026-10-14).
 */
final readonly class ProductRecommendations
{
    public const int LIMIT = 8;

    public function __construct(private CategoryTree $categoryTree) {}

    /**
     * @return Collection<int, Product>
     */
    public function for(Product $product): Collection
    {
        $rules = RecommendationRule::query()->selectRaw('count(*) as aggregate, max(updated_at) as changed_at')->toBase()->first();
        $key = sprintf('product-recommendations:%d:%s:%d:%s', $product->id, now()->format('o-W'), $rules->aggregate, $rules->changed_at);

        $ids = Cache::remember($key, now()->addWeek(), fn (): array => $this->pickIds($product));

        return Product::query()
            ->webVisible()
            ->whereKey($ids)
            ->get()
            ->sortBy(fn (Product $recommended): int|false => array_search($recommended->id, $ids, true))
            ->values();
    }

    /**
     * @return array<int, int>
     */
    private function pickIds(Product $product): array
    {
        $rules = RecommendationRule::query()
            ->where('is_active', true)
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('source_category_id')
                ->orWhereIn('source_category_id', $this->categoryIdsWithParents($product)))
            ->with('targetCategory')
            ->get()
            ->shuffle();

        $inStock = [];
        $others = [];

        foreach ($rules as $rule) {
            $candidates = $this->candidates($rule, $product);
            $inStock[] = $candidates->filter(fn (int $stock): bool => $stock > 0)->keys()->all();
            $others[] = $candidates->filter(fn (int $stock): bool => $stock <= 0)->keys()->all();
        }

        return array_slice(array_values(array_unique([...$this->interleave($inStock), ...$this->interleave($others)])), 0, self::LIMIT);
    }

    /**
     * Random products of the rule's target, in-stock first: product id => stock.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function candidates(RecommendationRule $rule, Product $product): \Illuminate\Support\Collection
    {
        $query = Product::query()->webVisible()->whereKeyNot($product->id);

        if ($rule->targetCategory instanceof Category) {
            $categoryIds = $this->categoryTree->descendantIds($rule->targetCategory);
            $query->whereHas('categories', fn (Builder $categories): Builder => $categories->whereIn('product_categories.id', $categoryIds));
        } elseif (filled($rule->target_code_prefix)) {
            $query->whereLike('product_code', addcslashes((string) $rule->target_code_prefix, '%_\\') . '%');
        } else {
            return collect();
        }

        return $query->inStockFirst()->inRandomOrder()->limit(self::LIMIT)->pluck('stock_quantity', 'id')
            ->map(fn (mixed $stock): int => (int) $stock);
    }

    /**
     * Takes one from each list in turn, so every rule gets a place.
     *
     * @param  array<int, array<int, int>>  $lists
     * @return array<int, int>
     */
    private function interleave(array $lists): array
    {
        $result = [];

        for ($index = 0; $lists !== []; $index++) {
            foreach ($lists as $listIndex => $list) {
                if (! array_key_exists($index, $list)) {
                    unset($lists[$listIndex]);

                    continue;
                }

                $result[] = $list[$index];
            }
        }

        return $result;
    }

    /**
     * The product's categories and every category above them.
     *
     * @return array<int, int>
     */
    private function categoryIdsWithParents(Product $product): array
    {
        $ids = $product->categories()->pluck('product_categories.id')->all();
        $frontier = $ids;

        while ($frontier !== []) {
            $frontier = array_diff(
                Category::query()->whereKey($frontier)->whereNotNull('category_id')->pluck('category_id')->all(),
                $ids,
            );
            $ids = [...$ids, ...$frontier];
        }

        return $ids;
    }
}
