<?php

declare(strict_types=1);

namespace App\View\Components\Layouts;

use App\Models\Category;
use App\Services\CategoryTree;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

/**
 * The site header: logo, the category menu (desktop dropdown and mobile
 * list), account links, quote list and cart.
 */
final class Navbar extends Component
{
    public function __construct(private readonly CategoryTree $categoryTree) {}

    /**
     * The main categories that have web-visible products, for both the
     * desktop dropdown and the mobile menu (queried once per render).
     *
     * @return Collection<int, Category>
     */
    public function menuCategories(): Collection
    {
        return once(fn (): Collection => $this->categoryTree->stocked(
            Category::query()->menuRoots()->get(['id', 'name', 'slug']),
        ));
    }

    public function render(): View
    {
        return view('components.layouts.navbar');
    }
}
