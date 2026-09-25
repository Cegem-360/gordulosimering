@props(['categories'])
@inject('categoryTree', 'App\Services\CategoryTree')

{{-- Recursive hover fly-out submenu. A submenu is revealed by its own parent
     <li> only (`[&:hover>ul]`), so the menu opens one level per hover. A
     `group-hover` variant would match any hovered ancestor and open every level
     of the tree at once. A list without further levels (the 41 brands) may
     scroll; one with submenus must not, because overflow would clip them.
     Closed menus are display:none, not invisible: an invisible menu still
     takes up layout space and made phone pages scroll sideways. --}}
@php
    $submenus = $categories->mapWithKeys(fn ($category) => [$category->id => $categoryTree->stocked($category->children)]);
@endphp
<ul @class([
    'hidden absolute left-full top-0 z-50 min-w-56 bg-white rounded-lg shadow-xl border border-gray-200 p-2',
    'max-h-[70vh] overflow-y-auto' => $submenus->every(fn ($children) => $children->isEmpty()),
])>
    @foreach ($categories as $category)
        <li class="relative [&:hover>ul]:block" wire:key="flyout-{{ $category->id }}">
            <a href="{{ route('categories.show', $category) }}"
                class="flex items-center gap-2 p-2 rounded-md text-xs text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                <span class="grow">{{ $category->name }}</span>
                @if ($submenus[$category->id]->isNotEmpty())
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                @endif
            </a>

            @if ($submenus[$category->id]->isNotEmpty())
                <x-category-flyout :categories="$submenus[$category->id]" />
            @endif
        </li>
    @endforeach
</ul>
