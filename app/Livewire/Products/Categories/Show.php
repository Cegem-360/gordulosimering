<?php

declare(strict_types=1);

namespace App\Livewire\Products\Categories;

use App\Models\Category;
use App\Models\Product;
use App\Services\CategoryTree;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
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
     * Direct child categories that hold products, in menu order, each with
     * the picture its tile shows.
     *
     * @return Collection<int, array{category: Category, image: ?string}>
     */
    #[Computed]
    public function subcategories(): Collection
    {
        $tree = resolve(CategoryTree::class);

        return $tree->stocked($this->category->children)
            ->map(fn (Category $subcategory): array => [
                'category' => $subcategory,
                'image' => $tree->coverImageUrl($subcategory),
            ]);
    }

    /**
     * Ancestor chain (root first) for the breadcrumb trail.
     *
     * @return array<int, Category>
     */
    #[Computed]
    public function breadcrumbs(): array
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
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $categoryIds = resolve(CategoryTree::class)->descendantIds($this->category);

        return Product::query()
            ->webVisible()
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
}
