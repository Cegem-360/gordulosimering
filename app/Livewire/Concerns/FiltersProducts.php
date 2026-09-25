<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryTree;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

/**
 * The filter sidebar shared by the product list and the category index:
 * stock, the real top-level categories and sizes. The category filter covers
 * each category's whole subtree. Sizes run into the ten thousands, so the
 * sidebar lists the most common ones and a search field finds the rest.
 */
trait FiltersProducts
{
    private const int SIZE_OPTION_LIMIT = 30;

    /** @var array{category: array<int, string>, size: array<int, string>, stock: array<int, string>} */
    public array $selectedFilters = [
        'category' => [],
        'size' => [],
        'stock' => [],
    ];

    public string $sizeSearch = '';

    /**
     * The web-visible products the filters narrow down, before any filter.
     *
     * @return Builder<Product>
     */
    abstract protected function filterableProducts(): Builder;

    public function updatedSelectedFilters(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<int, array{title: string, key: string, visible: int, items: array<int, array{name: string, value: string, count: int}>, search?: array{model: string, placeholder: string, empty: ?string}}>
     */
    #[Computed]
    public function filters(): array
    {
        $isSearchingSizes = mb_strlen(mb_trim($this->sizeSearch)) > 0;

        return [
            [
                'title' => 'Készlet',
                'key' => 'stock',
                'visible' => 5,
                'items' => [
                    ['name' => 'Készleten', 'value' => 'in_stock', 'count' => $this->filterableProducts()->where('minimum_stock', '>', 0)->count()],
                    ['name' => 'Rendelésre', 'value' => 'out_of_stock', 'count' => $this->outOfStock($this->filterableProducts())->count()],
                ],
            ],
            [
                'title' => 'Kategória',
                'key' => 'category',
                'visible' => 5,
                'items' => $this->categoryOptions(),
            ],
            [
                'title' => 'Méret',
                'key' => 'size',
                'visible' => $isSearchingSizes ? self::SIZE_OPTION_LIMIT : 5,
                'items' => $this->sizeOptions(),
                'search' => [
                    'model' => 'sizeSearch',
                    'placeholder' => 'Méret keresése, pl. 25x52',
                    'empty' => $isSearchingSizes ? 'Nincs ilyen méret.' : null,
                ],
            ],
        ];
    }

    protected function resetProductFilters(): void
    {
        $this->selectedFilters = ['category' => [], 'size' => [], 'stock' => []];
        $this->sizeSearch = '';
    }

    /**
     * @param  Builder<Product>  $query
     */
    protected function applySelectedFilters(Builder $query): void
    {
        if ($this->selectedFilters['category'] !== []) {
            $query->whereHas('categories', fn (Builder $categories) => $categories->whereIn('product_categories.id', $this->selectedCategoryIds()));
        }

        if ($this->selectedFilters['size'] !== []) {
            $query->whereIn('size', $this->selectedFilters['size']);
        }

        $stock = $this->selectedFilters['stock'];

        if ($stock !== [] && ! (in_array('in_stock', $stock, true) && in_array('out_of_stock', $stock, true))) {
            if (in_array('in_stock', $stock, true)) {
                $query->where('minimum_stock', '>', 0);
            } else {
                $this->outOfStock($query);
            }
        }
    }

    /**
     * @return array<int, array{name: string, value: string, count: int}>
     */
    private function categoryOptions(): array
    {
        $tree = app(CategoryTree::class);
        $roots = Category::query()->menuRoots()->where('name', '!=', Category::BRAND_ROOT_NAME)->get();

        return $tree->stocked($roots)
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'value' => (string) $category->id,
                'count' => $this->filterableProducts()
                    ->whereHas('categories', fn (Builder $categories) => $categories->whereIn('product_categories.id', $tree->descendantIds($category)))
                    ->count(),
            ])
            ->filter(fn (array $option): bool => $option['count'] > 0)
            ->values()
            ->all();
    }

    /**
     * The most common sizes, or those matching the size search. Selected
     * sizes always stay in the list so they can be unticked.
     *
     * @return array<int, array{name: string, value: string, count: int}>
     */
    private function sizeOptions(): array
    {
        $term = mb_trim($this->sizeSearch);

        $options = $this->sizeCounts()
            ->when($term !== '', fn (Builder $query) => $query->whereRaw(
                'REPLACE(size, \',\', \'.\') LIKE ?',
                ['%' . str_replace(['\\', '%', '_', ','], ['\\\\', '\\%', '\\_', '.'], $term) . '%'],
            ))
            ->orderByDesc('count')
            ->orderBy('size')
            ->limit(self::SIZE_OPTION_LIMIT)
            ->get();

        $missingSelected = array_diff($this->selectedFilters['size'], $options->pluck('size')->all());

        if ($missingSelected !== []) {
            $options = $this->sizeCounts()->whereIn('size', $missingSelected)->get()->concat($options);
        }

        return $options
            ->map(fn (Product $row): array => ['name' => $row->size, 'value' => $row->size, 'count' => (int) $row->getAttribute('count')])
            ->all();
    }

    /**
     * @return Builder<Product>
     */
    private function sizeCounts(): Builder
    {
        return $this->filterableProducts()
            ->select('size', DB::raw('count(*) as count'))
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->groupBy('size');
    }

    /**
     * The selected categories plus everything beneath them.
     *
     * @return array<int, int>
     */
    private function selectedCategoryIds(): array
    {
        $tree = app(CategoryTree::class);

        return Category::query()
            ->whereKey($this->selectedFilters['category'])
            ->get()
            ->flatMap(fn (Category $category): array => $tree->descendantIds($category))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function outOfStock(Builder $query): Builder
    {
        return $query->where(fn (Builder $query) => $query->whereNull('minimum_stock')->orWhere('minimum_stock', '<=', 0));
    }
}
