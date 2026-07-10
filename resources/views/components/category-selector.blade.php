@use('App\Models\Category')

@php
    $categories = Category::query()
        ->whereNull('category_id')
        ->with('children.children.children')
        ->orderBy('name')
        ->get();
@endphp

<div class="bg-white rounded-lg shadow-lg p-4 w-full">
    <h2 class="text-xl font-bold text-[#1e3a5f] mb-4 px-3">Termékeink</h2>

    <ul class="space-y-0">
        @forelse ($categories as $category)
            <li class="group/item relative" wire:key="category-{{ $category->id }}">
                <a href="{{ route('categories.show', $category) }}"
                    class="flex items-center gap-3 p-3 hover:bg-gray-200 rounded-lg transition-colors">
                    <span class="w-5 h-5 shrink-0">
                        <svg class="w-full h-full text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                    <span class="text-gray-700 grow text-xs">{{ $category->name }}</span>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transform transition-transform group-hover/item:translate-x-1"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                @if ($category->children->isNotEmpty())
                    <x-category-flyout :categories="$category->children" />
                @endif
            </li>
        @empty
            <p class="text-gray-500 text-sm px-3 py-2">Nincsenek kategóriák.</p>
        @endforelse
    </ul>
</div>
