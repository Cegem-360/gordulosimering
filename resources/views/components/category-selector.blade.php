@use('App\Models\Category')

@php
    $categories = Category::query()
        ->whereNull('category_id')
        ->orderBy('name')
        ->get(['id', 'name', 'slug']);
@endphp

<div class="bg-white rounded-lg shadow-lg p-4 w-full">
    <h2 class="text-xl font-bold text-[#1e3a5f] mb-4 px-3">Termékeink</h2>

    <div class="space-y-0">
        @forelse ($categories as $category)
            <a href="{{ route('categories.show', $category) }}" wire:key="category-{{ $category->id }}"
                class="flex items-center gap-3 p-3 hover:bg-gray-200 rounded-lg group transition-colors">
                <span class="w-5 h-5 shrink-0">
                    <svg class="w-full h-full text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
                <span class="text-gray-700 grow text-xs">{{ $category->name }}</span>
                <svg class="w-4 h-4 text-gray-400 shrink-0 transform transition-transform group-hover:translate-x-1"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @empty
            <p class="text-gray-500 text-sm px-3 py-2">Nincsenek kategóriák.</p>
        @endforelse
    </div>
</div>
