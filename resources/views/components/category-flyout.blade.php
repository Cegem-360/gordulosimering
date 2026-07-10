@props(['categories'])

{{-- Recursive hover fly-out submenu. Each <li> is its own `group/item`, so a
     nested submenu reacts to its immediate parent (nearest ancestor) — this keeps
     the cascade working for any depth (the tree is up to 4 levels). --}}
<ul
    class="invisible opacity-0 group-hover/item:visible group-hover/item:opacity-100 transition-opacity duration-150 absolute left-full top-0 z-50 min-w-56 max-h-[70vh] overflow-y-auto bg-white rounded-lg shadow-xl border border-gray-200 p-2">
    @foreach ($categories->sortBy('name') as $category)
        <li class="group/item relative" wire:key="flyout-{{ $category->id }}">
            <a href="{{ route('categories.show', $category) }}"
                class="flex items-center gap-2 p-2 rounded-md text-xs text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                <span class="grow">{{ $category->name }}</span>
                @if ($category->children->isNotEmpty())
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                @endif
            </a>

            @if ($category->children->isNotEmpty())
                <x-category-flyout :categories="$category->children" />
            @endif
        </li>
    @endforeach
</ul>
