@props(['category', 'image' => null])

{{-- A photo tile linking to a category: the subcategories on a category
     page and the featured categories on the homepage. --}}
@php($placeholder = Vite::asset('resources/images/product-placeholder.svg'))
<a href="{{ route('categories.show', $category) }}"
    {{ $attributes->class('flex flex-col bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:border-blue-300 transition-all group overflow-hidden') }}>
    <img src="{{ $image ?? $placeholder }}" alt="{{ $category->name }}"
        onerror="this.onerror = null; this.src = '{{ $placeholder }}'"
        class="w-full h-32 object-contain bg-white p-2" loading="lazy">
    <span class="flex items-center justify-between gap-2 p-4 border-t border-gray-100">
        <span class="text-sm font-medium text-gray-800 group-hover:text-blue-600">
            {{ $category->name }}
        </span>
        <svg class="w-4 h-4 text-gray-400 shrink-0 group-hover:translate-x-1 transition-transform"
            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </span>
</a>
