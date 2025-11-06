<?php
$product = (object) [
    'item_number' => '6205-2RS',
    'name' => '6205-2RS mélyhornyú golyóscsapágy gumi porvédővel',
    'brand' => 'SKF',
    'type' => 'Mélyhornyú golyóscsapágy',
    'sealing' => 'Mindkét oldalon gumi porvédő',
    'material' => 'Krómacél',
    'material_grade' => 'GCr15',
    'bore_diameter' => '25 mm',
    'outer_diameter' => '52 mm',
    'width' => '15 mm',
    'dynamic_load' => '14.8 kN',
    'static_load' => '7.8 kN',
    'max_speed' => '13000 rpm',
    'net_retail_price' => 3250,
    'in_stock' => true,
];
?>

<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumbs -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center space-x-2 text-sm">
                <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">Kezdőlap</a>
                <span class="text-gray-500">&gt;</span>
                <a href="{{ route('categories.index') }}" class="text-blue-600 hover:underline">Csapágyak</a>
                <span class="text-gray-500">&gt;</span>
                <a href="#" class="text-blue-600 hover:underline">Mélyhornyú golyóscsapágyak</a>
                <span class="text-gray-500">&gt;</span>
                <a href="#" class="text-blue-600 hover:underline">Tömített golyóscsapágyak</a>
                <span class="text-gray-500">&gt;</span>
                <span class="text-gray-700">{{ $product->item_number }}</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Product Title -->
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">
            {{ $product->name ?? 'Hatlapú belső kulcsnyílású csavar DIN 912 Rozsdamentes acél A2' }}
        </h1>

        <div class="grid lg:grid-cols-2 gap-8 mb-8">
            <!-- Left Column: Images -->
            <div x-data="{
                activeImage: 1,
                images: [1, 2, 3, 4],
                prev() { this.activeImage = this.activeImage === 1 ? this.images.length : this.activeImage - 1 },
                next() { this.activeImage = this.activeImage === this.images.length ? 1 : this.activeImage + 1 },
                goTo(index) { this.activeImage = index }
            }" class="flex gap-4">
                <!-- Thumbnails -->
                <div class="flex flex-col gap-3 py-2">
                    <template x-for="index in images" :key="index">
                        <button @click="goTo(index)" :class="{ 'ring-2 ring-blue-500': activeImage === index }"
                            class="w-20 h-20 bg-white rounded-lg border hover:border-blue-500 transition-colors overflow-hidden cursor-pointer">
                            <img :src="`{{ Vite::asset('resources/images/products/bearing-${index}.webp') }}`"
                                :alt="`SKF 6205-2RS mélyhornyú golyóscsapágy nézet ${index}`"
                                class="w-full h-full object-contain">
                        </button>
                    </template>
                </div>

                <!-- Main Image -->
                <div class="flex-1">
                    <div class="relative bg-white rounded-lg border p-4">
                        <div class="aspect-square relative">
                            <template x-for="index in images" :key="index">
                                <div x-show="activeImage === index"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95" class="absolute inset-0">
                                    <img :src="`{{ Vite::asset('resources/images/products/bearing-${index}.webp') }}`"
                                        :alt="`Product view ${index}`" class="w-full h-full object-contain">
                                </div>
                            </template>
                        </div>

                        <!-- Navigation Buttons -->
                        <button @click="prev"
                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg text-gray-800 cursor-pointer">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button @click="next"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg text-gray-800 cursor-pointer">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Info -->
            <div class="space-y-6">
                <!-- Product Specs -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="mb-4">
                            <span class="text-gray-600">Cikkszám</span>
                            <p class="font-medium">{{ $product->item_number }}</p>
                        </div>
                        <div class="mb-4">
                            <span class="text-gray-600">Márka</span>
                            <p class="font-medium">{{ $product->brand }}</p>
                        </div>
                        <div class="mb-4">
                            <span class="text-gray-600">Típus</span>
                            <p class="font-medium">{{ $product->type }}</p>
                        </div>
                        <div class="mb-4">
                            <span class="text-gray-600">Tömítés</span>
                            <p class="font-medium">{{ $product->sealing }}</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-4">
                            <span class="text-gray-600">Anyag</span>
                            <p class="font-medium">{{ $product->material }}</p>
                        </div>
                        <div class="mb-4">
                            <span class="text-gray-600">Anyagminőség</span>
                            <p class="font-medium">{{ $product->material_grade }}</p>
                        </div>
                        <div class="mb-4">
                            <span class="text-gray-600">Dinamikus terhelhetőség</span>
                            <p class="font-medium">{{ $product->dynamic_load }}</p>
                        </div>
                        <div class="mb-4">
                            <span class="text-gray-600">Statikus terhelhetőség</span>
                            <p class="font-medium">{{ $product->static_load }}</p>
                        </div>
                    </div>
                </div>

                <!-- Technical Drawing -->
                {{-- <div class="border rounded-lg p-4 bg-white">
                    <h3 class="font-semibold mb-3">Műszaki rajz</h3>
                    <img src="{{ Vite::asset('resources/images/products/51050-drawing.webp') }}"
                        alt="Technical drawing" class="w-full max-w-md mx-auto">
                </div> --}}

                <!-- View All Specs Link -->
                <div class="text-center">
                    <a href="#specifications" class="text-blue-600 hover:underline inline-flex items-center gap-2">
                        Tekintse meg az összes specifikációt
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Variants Selector -->
                <div x-data="{
                    open: false,
                    selectedVariant: '6205-2RS',
                    variants: [
                        { id: '6205-2RS', name: '6205-2RS - Mindkét oldalon gumi porvédő', price: 3250 },
                        { id: '6205-2Z', name: '6205-2Z - Mindkét oldalon fém porvédő', price: 3450 },
                        { id: '6205-N', name: '6205-N - Horonygyűrűs kivitel', price: 3150 },
                        { id: '6205', name: '6205 - Nyitott kivitel', price: 2950 }
                    ],
                    select(variantId) {
                        this.selectedVariant = variantId;
                        this.open = false;
                    }
                }" class="relative">
                    <button type="button" @click="open = !open"
                        class="w-full border-2 border-gray-300 rounded-lg py-3 px-4 flex items-center justify-between hover:border-gray-400 transition-colors"
                        :class="{ 'border-blue-500': open }">
                        <div class="flex flex-col items-start">
                            <span class="text-sm text-gray-500">Választott kivitel</span>
                            <span class="font-medium" x-text="variants.find(v => v.id === selectedVariant).name"></span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1" @click.away="open = false"
                        class="absolute z-10 mt-2 w-full bg-white border rounded-lg shadow-lg">
                        <div class="p-2 space-y-1">
                            <template x-for="variant in variants" :key="variant.id">
                                <button @click="select(variant.id)"
                                    class="w-full px-3 py-2 text-left rounded hover:bg-gray-200 transition-colors flex items-center justify-between group"
                                    :class="{ 'bg-blue-100': selectedVariant === variant.id }">
                                    <span x-text="variant.name" class="font-medium"></span>
                                    <span class="text-gray-500 group-hover:text-gray-700"
                                        x-text="new Intl.NumberFormat('hu-HU').format(variant.price) + ' Ft'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Add to Cart Section -->
                <div class="bg-white rounded-lg border p-4">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-xl font-bold">
                                {{ number_format($product->net_retail_price, 0, ',', ' ') }} Ft</p>
                            <p class="text-sm text-gray-600">Nettó listaár</p>
                        </div>
                        @if ($product->in_stock)
                            <span
                                class="bg-green-500 text-white px-2 py-1 rounded text-xs font-bold flex items-center gap-1">
                                <i class="fa fa-cube"></i> Készleten
                            </span>
                        @endif
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <label for="quantity" class="text-sm font-medium">Mennyiség:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1"
                                class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="button"
                            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 flex items-center justify-center gap-2">
                            <i class="fa fa-cart-plus"></i> Rendelés
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Specifications Section -->
        <div id="specifications" class="bg-white rounded-lg border p-6">
            <h2 class="text-xl font-bold mb-4">Részletes specifikációk</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ([
        'Cikkszám' => $product->item_number,
        'Márka' => $product->brand,
        'Típus' => $product->type,
        'Tömítés' => $product->sealing,
        'Anyag' => $product->material,
        'Anyagminőség' => $product->material_grade,
        'Belső átmérő' => $product->bore_diameter,
        'Külső átmérő' => $product->outer_diameter,
        'Szélesség' => $product->width,
        'Dinamikus terhelhetőség' => $product->dynamic_load,
        'Statikus terhelhetőség' => $product->static_load,
        'Maximális fordulatszám' => $product->max_speed,
    ] as $label => $value)
                    <div class="border-b pb-2">
                        <span class="text-gray-600 text-sm">{{ $label }}</span>
                        <p class="font-medium">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
