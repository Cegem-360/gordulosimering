@props(['categories', 'depth' => 0])
@inject('categoryTree', 'App\Services\CategoryTree')

{{-- Recursive accordion for the mobile menu: every category with stocked
     subcategories gets its own toggle, the name itself stays a link. --}}
<ul @class(['space-y-1', 'pl-4 border-l border-gray-200 ml-1' => $depth > 0])>
    @foreach ($categories as $category)
        @php($subcategories = $categoryTree->stocked($category->children))
        <li wire:key="mobile-category-{{ $category->id }}"
            @if ($subcategories->isNotEmpty()) x-data="{ open: false }" @endif>
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('categories.show', $category) }}"
                    @class([
                        'block grow py-2 hover:text-blue-600',
                        'text-gray-600' => $depth === 0,
                        'text-sm text-gray-500' => $depth > 0,
                    ])>{{ $category->name }}</a>
                @if ($subcategories->isNotEmpty())
                    <button type="button" @click="open = !open" :aria-expanded="open"
                        aria-label="{{ __(':category alkategóriái', ['category' => $category->name]) }}"
                        class="p-2 -mr-2 text-gray-500 hover:text-blue-600">
                        <i class="fas fa-chevron-down text-sm transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                @endif
            </div>

            @if ($subcategories->isNotEmpty())
                <div x-show="open" x-collapse x-cloak>
                    <x-mobile-category-list :categories="$subcategories" :depth="$depth + 1" />
                </div>
            @endif
        </li>
    @endforeach
</ul>
