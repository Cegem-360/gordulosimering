<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Category;
use App\Models\DiscountGroup;
use App\Models\Product;
use App\Services\CategoryTree;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;

/**
 * The filter sidebar shared by the product list and the category index:
 * stock, the real top-level categories, product groups, sizes, brands and
 * materials. The category filter covers each category's whole subtree. Sizes
 * run into the ten thousands, so the sidebar lists the most common ones and a
 * search field finds the rest.
 */
trait FiltersProducts
{
    private const int SIZE_OPTION_LIMIT = 30;

    private const array EMPTY_FILTERS = [
        'category' => [],
        'group' => [],
        'size' => [],
        'brand' => [],
        'material' => [],
        'stock' => [],
    ];

    private const array EMPTY_DIMENSIONS = [
        'inner_diameter' => null,
        'outer_diameter' => null,
        'width' => null,
    ];

    /**
     * Two sizes count as the same within this tolerance (mm); the columns
     * keep three decimals.
     */
    private const float DIMENSION_TOLERANCE = 0.0005;

    private const string DISCONTINUED_GROUP_NAME = 'Megszűnt termék';

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

    /** @var array{category: array<int, string>, group: array<int, string>, size: array<int, string>, brand: array<int, string>, material: array<int, string>, stock: array<int, string>} */
    public array $selectedFilters = self::EMPTY_FILTERS;

    public string $sizeSearch = '';

    /**
     * The exact size wanted for each dimension in mm, as the inputs send it;
     * dimensionValue() reads it.
     *
     * @var array<string, mixed>
     */
    public array $dimensions = self::EMPTY_DIMENSIONS;

    /**
     * The web-visible products the filters narrow down, before any filter.
     *
     * @return Builder<Product>
     */
    abstract protected function filterableProducts(): Builder;

    /**
     * A page opened before a deploy may send back filter state without the
     * newer keys; fill them in so the filters never read a missing key.
     */
    public function hydrateFiltersProducts(): void
    {
        $this->normaliseFilterState();
    }

    public function updatedSelectedFilters(): void
    {
        $this->normaliseFilterState();
        $this->resetPage();
    }

    public function updatedDimensions(): void
    {
        $this->normaliseFilterState();
        $this->resetPage();
    }

    public function clearDimension(string $dimension): void
    {
        if (! array_key_exists($dimension, self::DIMENSIONS)) {
            return;
        }

        $this->dimensions[$dimension] = null;
        $this->resetPage();
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    #[Computed]
    public function dimensionChips(): array
    {
        $chips = [];

        foreach (self::DIMENSIONS as $column => $labels) {
            $value = $this->dimensionValue($column);

            if ($value !== null) {
                $chips[] = ['key' => $column, 'label' => "{$labels['chip']}: {$this->formatMillimetres($value)} mm"];
            }
        }

        return $chips;
    }

    /**
     * @return array<int, array{title: string, key: string, visible: int, items: array<int, array{name: string, value: string, count: int}>, type?: 'dimensions', fields?: array<int, array{key: string, label: string}>, search?: array{model: string, placeholder: string, empty: ?string}}>
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
                    ['name' => 'Készleten', 'value' => 'in_stock', 'count' => $this->filterableProducts()->where('stock_quantity', '>', 0)->count()],
                    ['name' => 'Rendelésre', 'value' => 'out_of_stock', 'count' => $this->outOfStock($this->filterableProducts())->count()],
                ],
            ],
            ...($this->showsCategoryFilter() ? [[
                'title' => 'Kategória',
                'key' => 'category',
                'visible' => 5,
                'items' => $this->categoryOptions(),
            ]] : []),
            [
                'title' => 'Termékcsoport',
                'key' => 'group',
                'visible' => 5,
                'items' => $this->groupOptions(),
            ],
            [
                'title' => 'Méretek (mm)',
                'key' => 'dimensions',
                'type' => 'dimensions',
                'visible' => 0,
                'items' => [],
                'fields' => collect(self::DIMENSIONS)
                    ->map(fn (array $labels, string $column): array => ['key' => $column, 'label' => $labels['sidebar']])
                    ->values()
                    ->all(),
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
        ];
    }

    /**
     * A category page has its subcategory tiles instead of the category filter.
     */
    protected function showsCategoryFilter(): bool
    {
        return true;
    }

    protected function resetProductFilters(): void
    {
        $this->selectedFilters = self::EMPTY_FILTERS;
        $this->sizeSearch = '';
        $this->dimensions = self::EMPTY_DIMENSIONS;
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

        if ($this->selectedFilters['group'] !== []) {
            $query->whereIn('group_code', DiscountGroup::query()->whereIn('name', $this->selectedFilters['group'])->pluck('code'));
        }

        foreach (['brand', 'material'] as $column) {
            if ($this->selectedFilters[$column] !== []) {
                $query->whereIn($column, $this->selectedFilters[$column]);
            }
        }

        foreach (array_keys(self::DIMENSIONS) as $column) {
            $value = $this->dimensionValue($column);

            if ($value !== null) {
                $query->whereBetween($column, [$value - self::DIMENSION_TOLERANCE, $value + self::DIMENSION_TOLERANCE]);
            }
        }

        $stock = $this->selectedFilters['stock'];

        if ($stock !== [] && ! (in_array('in_stock', $stock, true) && in_array('out_of_stock', $stock, true))) {
            if (in_array('in_stock', $stock, true)) {
                $query->where('stock_quantity', '>', 0);
            } else {
                $this->outOfStock($query);
            }
        }
    }

    private function normaliseFilterState(): void
    {
        $selected = array_merge(self::EMPTY_FILTERS, $this->selectedFilters);

        foreach (self::EMPTY_FILTERS as $key => $empty) {
            $selected[$key] = is_array($selected[$key]) ? $selected[$key] : $empty;
        }

        $this->selectedFilters = $selected;

        $this->dimensions = array_merge(
            self::EMPTY_DIMENSIONS,
            array_intersect_key(is_array($this->dimensions) ? $this->dimensions : [], self::EMPTY_DIMENSIONS),
        );
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
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * The size given for a dimension, or null when it is not a non-negative
     * number; a decimal comma is fine.
     */
    private function dimensionValue(string $column): ?float
    {
        return $this->millimetres($this->dimensions[$column] ?? null);
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
}
