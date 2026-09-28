@use('App\Models\Category')
@inject('categoryTree', 'App\Services\CategoryTree')

{{-- Categories ticked as "Kiemelt kategória" in the admin, in their menu
     order. Hidden until at least one is ticked, and a featured category
     without web-visible products drops out like it does in the menus. --}}
@php
    $categories = $categoryTree->stocked(Category::query()->featured()->ordered()->get());
@endphp

@if ($categories->isNotEmpty())
    <section class="py-8">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">Kiemelt kategóriáink</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach ($categories as $category)
                    <x-category-tile :category="$category" :image="$categoryTree->coverImageUrl($category)"
                        wire:key="featured-category-{{ $category->id }}" />
                @endforeach
            </div>
        </div>
    </section>
@endif
