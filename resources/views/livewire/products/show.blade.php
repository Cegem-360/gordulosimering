<div class="min-h-screen bg-gray-50">

    <!-- Product Database Information -->
    @if ($product)
        <!-- Breadcrumbs -->
        <div class="bg-white border-b">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center flex-wrap gap-2 text-sm">
                    <a href="{{ route('index') }}" class="text-blue-600 hover:underline">Kezdőlap</a>
                    <span class="text-gray-500">&gt;</span>
                    <a href="{{ route('categories.index') }}" class="text-blue-600 hover:underline">Termékkategóriák</a>
                    @if ($category = $product->categories->first())
                        <span class="text-gray-500">&gt;</span>
                        <a href="{{ route('categories.show', $category) }}"
                            class="text-blue-600 hover:underline">{{ $category->name }}</a>
                    @endif
                    <span class="text-gray-500">&gt;</span>
                    <span class="text-gray-700">{{ $product->name ?? $product->product_code }}</span>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-5">
            <!-- Product Title -->
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                {{ $product->name ?? 'Termék' }}
            </h1>

            {{-- On wide screens the photo box takes the height of the boxes beside it, so the
                 page fits a 100% zoom laptop screen without scrolling (client, 2026-10-13). --}}
            <div class="grid lg:grid-cols-2 gap-6 mb-8">
                <!-- Left Column: Images -->
                @php
                    $productImages = $product->gallery_urls;
                    $imageCount = count($productImages);
                @endphp
                <div x-data="{
                    activeImage: 0,
                    images: {{ json_encode($productImages) }},
                    imageCount: {{ $imageCount }},
                    prev() { this.activeImage = this.activeImage === 0 ? this.imageCount - 1 : this.activeImage - 1 },
                    next() { this.activeImage = this.activeImage === this.imageCount - 1 ? 0 : this.activeImage + 1 },
                    goTo(index) { this.activeImage = index }
                }" class="flex gap-4">
                    <!-- Thumbnails -->
                    @if ($imageCount > 1)
                        <div class="flex flex-col gap-3 py-2">
                            @foreach ($productImages as $index => $image)
                                <button @click="goTo({{ $index }})"
                                    :class="{ 'ring-2 ring-blue-500': activeImage === {{ $index }} }"
                                    class="w-20 h-20 bg-white rounded-lg border hover:border-blue-500 transition-colors overflow-hidden cursor-pointer">
                                    <img src="{{ $image }}"
                                        alt="{{ $product->name }} nézet {{ $index + 1 }}"
                                        class="w-full h-full object-contain">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Main Image -->
                    <div class="flex-1">
                        <div class="relative bg-white rounded-lg border p-4 lg:h-full lg:min-h-80">
                            <div class="aspect-square relative lg:aspect-auto lg:h-full">
                                @if ($imageCount > 0)
                                    @foreach ($productImages as $index => $image)
                                        <div x-show="activeImage === {{ $index }}"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95" class="absolute inset-0">
                                            <img src="{{ $image }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-contain">
                                        </div>
                                    @endforeach
                                @else
                                    <img src="{{ Vite::asset('resources/images/product-placeholder.svg') }}"
                                        alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-contain">
                                @endif
                            </div>

                            <!-- Navigation Buttons -->
                            @if ($imageCount > 1)
                                <button @click="prev"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg text-gray-800 cursor-pointer">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button @click="next"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full shadow-lg text-gray-800 cursor-pointer">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: Product Info -->
                <div class="space-y-4">
                    {{-- Csak az ügyfél által engedett adatok (2026-10-13): termékkód, név (a címben),
                         méret, súly, minőség; mellette a mennyiségi egység. --}}
                    <div class="bg-white rounded-lg border p-5">
                        @if ($product->product_code)
                            <div class="mb-4">
                                <span class="inline-block bg-blue-50 border border-blue-200 text-gray-900 px-3 py-1.5 rounded-md text-xl font-bold tracking-wide">
                                    {{ $product->product_code }}
                                </span>
                            </div>
                        @endif

                        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            @if ($product->size)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-ruler text-gray-400 w-4"></i>
                                    <dt class="text-gray-500">Méret:</dt>
                                    <dd class="text-gray-700 font-medium">{{ $product->size }}</dd>
                                </div>
                            @endif
                            @if ((float) $product->weight > 0)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-weight-hanging text-gray-400 w-4"></i>
                                    <dt class="text-gray-500">Súly:</dt>
                                    <dd class="text-gray-700 font-medium">{{ Number::format((float) $product->weight, maxPrecision: 3, locale: 'hu') }} kg</dd>
                                </div>
                            @endif
                            @if ($product->visibleField('quality'))
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-star text-gray-400 w-4"></i>
                                    <dt class="text-gray-500">Minőség:</dt>
                                    <dd class="text-gray-700 font-medium">{{ $product->visibleField('quality') }}</dd>
                                </div>
                            @endif
                            @if ($product->quantity_unit)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-boxes text-gray-400 w-4"></i>
                                    <dt class="text-gray-500">Egység:</dt>
                                    <dd class="text-gray-700 font-medium">{{ $product->quantity_unit }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Add to Cart Section -->
                    <div class="bg-white rounded-lg border-2 border-blue-100 p-5 shadow-sm">
                        <!-- Stock Badge -->
                        <div class="flex items-center justify-end gap-3 mb-2">
                            @if ($product->isInStock())
                                <span class="text-sm text-gray-600">Raktáron: <strong class="text-gray-900">{{ $product->stockQuantityLabel() }}</strong></span>
                                <span
                                    class="bg-green-500 text-white px-2 py-1 rounded text-xs font-bold flex items-center gap-1">
                                    <i class="fa fa-cube"></i> Készleten
                                </span>
                            @else
                                <span
                                    class="bg-orange-500 text-white px-2 py-1 rounded text-xs font-bold flex items-center gap-1">
                                    <i class="fa fa-clock"></i> Rendelésre
                                </span>
                            @endif
                        </div>

                        <!-- Price Section -->
                        <div class="mb-3">
                            @if ($product->net_selling_price)
                                <div class="flex flex-wrap items-baseline gap-x-2">
                                    <x-product-price :product="$product" size="lg" />
                                    <span class="text-3xl md:text-4xl font-light text-blue-600">+ÁFA</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Nettó eladási ár</p>
                                @if ($product->gross_selling_price && ! $product->hasDiscount())
                                    <p class="text-sm text-gray-400 mt-1">
                                        Bruttó: {{ Number::currency($product->gross_selling_price, 'HUF', 'hu', 0) }}
                                    </p>
                                @endif
                            @elseif ($product->gross_selling_price)
                                <p class="text-3xl md:text-4xl font-bold text-blue-600">
                                    {{ Number::currency($product->gross_selling_price, 'HUF', 'hu', 0) }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">Bruttó eladási ár</p>
                            @else
                                <p class="text-xl text-gray-500">Érdeklődjön az árról</p>
                            @endif
                        </div>

                        {{-- Mennyiség: a kosárhoz és az ajánlatkéréshez is ez számít. --}}
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mennyiség</label>
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="decrement"
                                    class="w-11 h-11 rounded-l-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xl flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                                    <i class="fas fa-minus text-sm"></i>
                                </button>
                                <input type="number" wire:model.live.blur="quantity" name="quantity"
                                    min="{{ $product->minimumOrderQuantity() }}" max="9999" step="{{ $product->orderQuantityStep() }}"
                                    class="w-20 h-11 text-center text-lg font-semibold border-y border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" wire:click="increment"
                                    class="w-11 h-11 rounded-r-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xl flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                            @php
                                $step = $product->orderQuantityStep();
                                $minimum = $product->minimumOrderQuantity();
                                $unit = mb_trim((string) $product->quantity_unit) ?: 'db';
                            @endphp
                            @if ($step > 1)
                                <p class="text-xs text-gray-500 mt-2">
                                    Rendelési egység: {{ $step }} {{ $unit }}. Csak ennek többszöröse rendelhető
                                    ({{ $minimum }}, {{ $minimum + $step }}, {{ $minimum + 2 * $step }} …).
                                </p>
                            @endif
                        </div>

                        @if ($product->isInStock())
                            <!-- Order Button -->
                            <button type="button" wire:click="addToCart"
                                class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 font-semibold text-lg transition-colors shadow-sm">
                                <i class="fa fa-cart-plus"></i> Kosárba
                            </button>

                            <!-- Contact Option -->
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                <a href="tel:+3612611566"
                                    class="text-md text-gray-500 hover:text-blue-600 inline-flex items-center gap-1">
                                    <i class="fas fa-phone text-xs"></i>
                                    Kérdése van? Hívjon minket!
                                </a>
                                <x-quote-request-button :in-list="$this->isInQuoteList"
                                    class="bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 font-semibold" />
                            </div>
                        @else
                            {{-- Rendelésre: nincs kosár, csak hívás vagy ajánlatkérés, mint a kártyán. --}}
                            <div class="grid sm:grid-cols-2 gap-3">
                                <a href="tel:+3612611566" title="Hívjon: +36 1 261 1566"
                                    class="bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 flex items-center justify-center gap-2 font-semibold text-lg transition-colors">
                                    <i class="fa fa-phone"></i> Hívjon
                                </a>
                                <x-quote-request-button :in-list="$this->isInQuoteList"
                                    class="bg-white text-blue-600 border border-blue-600 py-3 rounded-lg hover:bg-blue-50 font-semibold text-lg" />
                            </div>
                        @endif
                    </div>

                    {{-- Dokumentumok --}}
                    @if ($documents = $product->documentLinks())
                        <div class="bg-white rounded-lg border p-6">
                            <h3 class="text-lg font-semibold mb-4 pb-2 border-b flex items-center gap-2">
                                <i class="fas fa-file-pdf text-gray-600"></i>
                                Dokumentumok
                            </h3>
                            <ul class="space-y-2">
                                @foreach ($documents as $document)
                                    <li>
                                        <a href="{{ $document['url'] }}" target="_blank" rel="noopener"
                                            class="inline-flex items-center gap-2 text-blue-600 hover:underline break-all">
                                            <i class="fas fa-file-arrow-down text-sm"></i>
                                            {{ $document['name'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="container mx-auto px-4 py-8">
            <div class="bg-yellow-100 border border-yellow-400 rounded-lg p-6">
                <p class="text-yellow-800">Nincs valós termék betöltve az adatbázisból. Kérjük, győződjön meg arról,
                    hogy a route megfelelően van beállítva és van termék az adatbázisban.</p>
            </div>
        </div>
    @endif
</div>
