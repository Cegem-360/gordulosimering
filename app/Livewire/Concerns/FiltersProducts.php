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

    private const array EMPTY_RANGE = ['min' => null, 'max' => null];

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
     * The "from" and "to" of each dimension in mm, as the inputs send them;
     * dimensionBounds() reads them.
     *
     * @var array<string, array{min: mixed, max: mixed}>
     */
    public array $dimensionRanges = [
        'inner_diameter' => self::EMPTY_RANGE,
        'outer_diameter' => self::EMPTY_RANGE,
        'width' => self::EMPTY_RANGE,
    ];

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

    public function updatedDimensionRanges(): void
    {
        $this->normaliseFilterState();
        $this->resetPage();
    }

    public function clearDimensionRange(string $dimension): void
    {
        if (! array_key_exists($dimension, self::DIMENSIONS)) {
            return;
        }

        $this->dimensionRanges[$dimension] = self::EMPTY_RANGE;
        $this->resetPage();
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    #[Computed]
    public function dimensionRangeChips(): array
    {
        $chips = [];

        foreach (self::DIMENSIONS as $column => $labels) {
            [$min, $max] = $this->dimensionBounds($column);

            $text = match (true) {
                $min !== null && $max !== null => $this->formatMillimetres($min) . '–' . $this->formatMillimetres($max) . ' mm',
                $min !== null => $this->formatMillimetres($min) . ' mm-től',
                $max !== null => $this->formatMillimetres($max) . ' mm-ig',
                default => null,
            };

            if ($text !== null) {
                $chips[] = ['key' => $column, 'label' => "{$labels['chip']}: {$text}"];
            }
        }

        return $chips;
    }

    /**
     * @return array<int, array{title: string, key: string, visible: int, items: array<int, array{name: string, value: string, count: int}>, type?: 'range', ranges?: array<int, array{key: string, label: string, min: ?float, max: ?float}>, search?: array{model: string, placeholder: string, empty: ?string}}>
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
            [
                'title' => 'Kategória',
                'key' => 'category',
                'visible' => 5,
                'items' => $this->categoryOptions(),
            ],
            [
                'title' => 'Termékcsoport',
                'key' => 'group',
                'visible' => 5,
                'items' => $this->groupOptions(),
            ],
            [
                'title' => 'Méretek (mm)',
                'key' => 'dimensions',
                'type' => 'range',
                'visible' => 0,
                'items' => [],
                'ranges' => $this->dimensionRangeOptions(),
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

    protected function resetProductFilters(): void
    {
        $this->selectedFilters = self::EMPTY_FILTERS;
        $this->sizeSearch = '';
        $this->dimensionRanges = array_map(fn (): array => self::EMPTY_RANGE, self::DIMENSIONS);
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
            [$min, $max] = $this->dimensionBounds($column);

            if ($min !== null) {
                $query->where($column, '>=', $min);
            }

            if ($max !== null) {
                $query->where($column, '<=', $max);
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

        $this->dimensionRanges = array_map(
            fn (string $column): array => array_merge(
                self::EMPTY_RANGE,
                is_array($this->dimensionRanges[$column] ?? null) ? $this->dimensionRanges[$column] : [],
            ),
            array_combine(array_keys(self::DIMENSIONS), array_keys(self::DIMENSIONS)),
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
     * The smallest and largest value of each dimension in the unfiltered list.
     *
     * @return array<int, array{key: string, label: string, min: ?float, max: ?float}>
     */
    private function dimensionRangeOptions(): array
    {
        $limits = $this->filterableProducts()
            ->selectRaw(collect(array_keys(self::DIMENSIONS))
                ->map(fn (string $column): string => "min({$column}) as {$column}_min, max({$column}) as {$column}_max")
                ->implode(', '))
            ->toBase()
            ->first();

        return collect(self::DIMENSIONS)
            ->map(fn (array $labels, string $column): array => [
                'key' => $column,
                'label' => $labels['sidebar'],
                'min' => $limits?->{"{$column}_min"} === null ? null : (float) $limits->{"{$column}_min"},
                'max' => $limits?->{"{$column}_max"} === null ? null : (float) $limits->{"{$column}_max"},
            ])
            ->values()
            ->all();
    }

    /**
     * The valid bounds of a dimension, the lower one first. A bound that is
     * not a non-negative number counts as not given; a decimal comma is fine.
     *
     * @return array{0: ?float, 1: ?float}
     */
    private function dimensionBounds(string $column): array
    {
        $min = $this->millimetres($this->dimensionRanges[$column]['min'] ?? null);
        $max = $this->millimetres($this->dimensionRanges[$column]['max'] ?? null);

        if ($min !== null && $max !== null && $min > $max) {
            return [$max, $min];
        }

        return [$min, $max];
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
