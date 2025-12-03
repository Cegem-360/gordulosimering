@use('App\Models\Product')

<!-- Real Products from Database -->
<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Legújabb termékeink</h2>
            <p class="text-gray-600">Valós termékadatok a products táblából</p>
        </div>

        @php
            $products = Product::query()->inRandomOrder()->latest()->limit(10)->get();
        @endphp

        @if ($products->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach ($products as $product)
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
