<?php

declare(strict_types=1);

namespace App\Livewire\Products\Categories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

final class Show extends Component
{
    use WithPagination;

    public Category $category;

    public function mount(Category $category): void
    {
        $this->category = $category;
    }

    /**
     * Direct child categories, shown as sub-navigation cards.
     */
    public function getSubcategoriesProperty(): Collection
    {
        return $this->category->children()->orderBy('name')->get();
    }

    /**
     * Ancestor chain (root first) for the breadcrumb trail.
     *
     * @return array<int, Category>
     */
    public function getBreadcrumbsProperty(): array
    {
        $trail = [];
        $node = $this->category;

        while ($node instanceof Category) {
            $trail[] = $node;
            $node = $node->parentCategory;
        }

        return array_reverse($trail);
    }

    /**
     * Products linked to this category or any of its descendants.
     */
    public function getProductsProperty(): LengthAwarePaginator
    {
        $categoryIds = $this->descendantIds();

        return Product::query()
            ->whereHas('categories', fn ($query) => $query->whereIn('product_categories.id', $categoryIds))
            ->orderBy('name')
            ->paginate(24);
    }

    public function render(): Factory|View
    {
        return view('livewire.products.categories.show', [
            'breadcrumbs' => $this->breadcrumbs,
            'subcategories' => $this->subcategories,
            'products' => $this->products,
        ]);
    }

    /**
     * The current category id plus every descendant id (tree is up to 4 deep).
     *
     * @return array<int, int>
     */
    private function descendantIds(): array
    {
        $byParent = Category::query()->get(['id', 'category_id'])->groupBy('category_id');

        $ids = [$this->category->id];
        $stack = [$this->category->id];

        while ($stack !== []) {
            $parentId = array_pop($stack);

            foreach ($byParent->get($parentId, collect()) as $child) {
                $ids[] = $child->id;
                $stack[] = $child->id;
            }
        }

        return $ids;
    }
}
