<section class="py-8 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Legújabb termékeink</h2>
            <p class="text-gray-600">Tekintse meg legnépszerűbb termékeink választékát</p>
        </div>

        @php
            $products = \App\Models\Product::query()
                ->inRandomOrder()
                ->limit(10)
                ->get();
        @endphp

        <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">
            @foreach ($products as $product)
                <livewire:product-card :product="$product" :wire:key="'product-'.$product->id" />
            @endforeach
        </div>
    </div>
</section>
