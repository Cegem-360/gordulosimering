@use('App\Models\Product')

{{-- <x-dummy-prd /> --}}

<!-- Real Products from Database -->
<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Legújabb termékeink</h2>
            <p class="text-gray-600">Valós termékadatok a products táblából</p>
        </div>

        @php
            $realProducts = Product::query()
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($product) {
                    // Transform database product to match the expected structure
                    return (object) [
                        'id' => $product->id,
                        'slug' => $product->slug,
                        'item_name' => $product->name ?? 'Nincs név',
                        'manufacturer' => (object) ['name' => $product->supplier ?? 'Nincs beszállító'],
                        'net_retail_price' => $product->net_selling_price ?? 0,
                        'main_image' => $product->images[0] ?? Vite::asset('resources/images/bearing.webp'),
                        'all_quantity' => $product->minimum_stock ?? 0,
                        'min_order_quantity' => $product->min_order_quantity ?? 1,
                        'in_stock' => ($product->minimum_stock ?? 0) > 0,
                    ];
                });
        @endphp

        @if ($realProducts->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach ($realProducts as $product)
                    <livewire:product-card :product="$product" :wire:key="'product-'.$product->id" />
                @endforeach
            </div>
        @else
            <div class="bg-yellow-100 border border-yellow-400 rounded-lg p-6">
                <p class="text-yellow-800">
                    <i class="fa fa-exclamation-triangle mr-2"></i>
                    Nincsenek termékek az adatbázisban. Kérjük, töltse fel a products táblát adatokkal.
                </p>
            </div>
        @endif
    </div>
</section>
