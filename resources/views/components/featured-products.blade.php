@use('App\Models\Product')

{{-- Products ticked as "Kiemelt termék" in the admin; up to ten, two rows.
     Hidden until at least one is ticked. It used to list ten random products
     as "Legkeresettebb termékeink", which nothing measured. --}}
@php
    $products = Product::query()->webVisible()->featured()->inStockFirst()->orderBy('name')->limit(10)->get();
@endphp

@if ($products->isNotEmpty())
    <section class="py-8 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-6">Kiemelt termékeink</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach ($products as $product)
                    <livewire:product-card :product="$product" :wire:key="'featured-product-'.$product->id" />
                @endforeach
            </div>
        </div>
    </section>
@endif
