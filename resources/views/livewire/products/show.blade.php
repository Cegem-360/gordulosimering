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
                    @if ($product->product_variety)
                        <span class="text-gray-500">&gt;</span>
                        <a href="{{ route('categories.index') }}"
                            class="text-blue-600 hover:underline">{{ $product->product_variety }}</a>
                    @endif
                    <span class="text-gray-500">&gt;</span>
                    <span class="text-gray-700">{{ $product->name ?? $product->product_code }}</span>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Product Title -->
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">
                {{ $product->name ?? 'Termék' }}
            </h1>

            <div class="grid lg:grid-cols-2 gap-8 mb-8">
                <!-- Left Column: Images -->
                @php
                    $productImages = $product->images ?? [];
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
                        <div class="relative bg-white rounded-lg border p-4">
                            <div class="aspect-square relative">
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
                                    <img src="{{ Vite::asset('resources/images/bearing.webp') }}"
                                        alt="{{ $product->name }}" class="w-full h-full object-contain">
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
                <div class="space-y-6">
                    <!-- Product Quick Info Card -->
                    <div class="bg-white rounded-lg border p-5">
                        <!-- Product Code Badge -->
                        @if ($product->product_code)
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-mono">
                                    {{ $product->product_code }}
                                </span>
                                @if ($product->supplier)
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                        {{ $product->supplier }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <!-- Key Specs Grid -->
                        <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            @if ($product->catalog_number)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-hashtag text-gray-400 w-4"></i>
                                    <span class="text-gray-500">Katalógus:</span>
                                    <span class="text-gray-700 font-medium">{{ $product->catalog_number }}</span>
                                </div>
                            @endif
                            @if ($product->product_variety)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-folder text-gray-400 w-4"></i>
                                    <span class="text-gray-500">Kategória:</span>
                                    <span class="text-gray-700 font-medium">{{ $product->product_variety }}</span>
                                </div>
                            @endif
                            @if ($product->type)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-cog text-gray-400 w-4"></i>
                                    <span class="text-gray-500">Típus:</span>
                                    <span class="text-gray-700 font-medium">{{ $product->type }}</span>
                                </div>
                            @endif
                            @if ($product->size)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-ruler text-gray-400 w-4"></i>
                                    <span class="text-gray-500">Méret:</span>
                                    <span class="text-gray-700 font-medium">{{ $product->size }}</span>
                                </div>
                            @endif
                            @if ($product->quality)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-star text-gray-400 w-4"></i>
                                    <span class="text-gray-500">Minőség:</span>
                                    <span class="text-gray-700 font-medium">{{ $product->quality }}</span>
                                </div>
                            @endif
                            @if ($product->weight)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-weight-hanging text-gray-400 w-4"></i>
                                    <span class="text-gray-500">Súly:</span>
                                    <span class="text-gray-700 font-medium">{{ $product->weight }} kg</span>
                                </div>
                            @endif
                            @if ($product->quantity_unit)
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-boxes text-gray-400 w-4"></i>
                                    <span class="text-gray-500">Egység:</span>
                                    <span class="text-gray-700 font-medium">{{ $product->quantity_unit }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- View All Specs Link -->
                        <div class="mt-4 pt-4 border-t">
                            <a href="#specifications"
                                class="text-blue-600 hover:underline inline-flex items-center gap-2 text-sm">
                                <i class="fas fa-list-ul"></i>
                                Összes specifikáció megtekintése
                                <i class="fas fa-chevron-down text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Add to Cart Section -->
                    <div class="bg-white rounded-lg border-2 border-blue-100 p-5 shadow-sm">
                        <!-- Stock Badge -->
                        <div class="flex justify-end mb-2">
                            @if (($product->minimum_stock ?? 0) > 0)
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
                        <div class="mb-5">
                            @if ($product->net_selling_price)
                                <p class="text-3xl md:text-4xl font-bold text-blue-600">
                                    {{ Number::currency($product->net_selling_price, 'HUF', 'hu', 0) }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">Nettó eladási ár</p>
                                @if ($product->gross_selling_price)
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

                        <!-- Quantity Selector -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mennyiség</label>
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="decrement"
                                    class="w-12 h-12 rounded-l-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xl flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                                    <i class="fas fa-minus text-sm"></i>
                                </button>
                                <input type="number" wire:model.live="quantity" name="quantity"
                                    min="{{ $product->min_order_quantity ?? 1 }}" max="9999" step="1"
                                    class="w-20 h-12 text-center text-lg font-semibold border-y border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" wire:click="increment"
                                    class="w-12 h-12 rounded-r-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xl flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                            @if (($product->min_order_quantity ?? 1) > 1)
                                <p class="text-xs text-gray-500 mt-2">
                                    Min. rendelési mennyiség: {{ $product->min_order_quantity }}
                                </p>
                            @endif
                        </div>

                        <!-- Order Button -->
                        <button type="button" wire:click="addToCart"
                            class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 font-semibold text-lg transition-colors shadow-sm">
                            <i class="fa fa-cart-plus"></i> Kosárba
                        </button>

                        <!-- Contact Option -->
                        <div class="mt-3">
                            <a href="#"
                                class="text-md text-gray-500 hover:text-blue-600 inline-flex items-center gap-1">
                                <i class="fas fa-phone text-xs"></i>
                                Kérdése van? Hívjon minket!
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications Section -->
            <div id="specifications" class="space-y-6">
                <h2 class="text-2xl font-bold">Termékinformációk</h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Alapadatok -->
                    <div class="bg-white rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 pb-2 border-b flex items-center gap-2">
                            <i class="fas fa-box text-gray-500"></i>
                            Alapadatok
                        </h3>
                        <dl class="space-y-3">
                            @if ($product->product_code)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Termékkód</dt>
                                    <dd class="font-medium text-right">{{ $product->product_code }}</dd>
                                </div>
                            @endif
                            @if ($product->name)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Megnevezés</dt>
                                    <dd class="font-medium text-right max-w-[60%]">{{ $product->name }}</dd>
                                </div>
                            @endif
                            @if ($product->catalog_number)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Katalógus szám</dt>
                                    <dd class="font-medium text-right">{{ $product->catalog_number }}</dd>
                                </div>
                            @endif
                            @if ($product->group_code)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Csoport kód</dt>
                                    <dd class="font-medium text-right">{{ $product->group_code }}</dd>
                                </div>
                            @endif
                            @if ($product->product_variety)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Termék kategória</dt>
                                    <dd class="font-medium text-right">{{ $product->product_variety }}</dd>
                                </div>
                            @endif
                            @if ($product->type)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Típus</dt>
                                    <dd class="font-medium text-right">{{ $product->type }}</dd>
                                </div>
                            @endif
                            @if ($product->size)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Méret</dt>
                                    <dd class="font-medium text-right">{{ $product->size }}</dd>
                                </div>
                            @endif
                            @if ($product->weight)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Súly</dt>
                                    <dd class="font-medium text-right">{{ $product->weight }} kg</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Szolgáltatás</dt>
                                <dd class="font-medium text-right">
                                    @if ($product->is_service)
                                        <span class="text-green-600">Igen</span>
                                    @else
                                        <span class="text-gray-500">Nem</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Árazás -->
                    <div class="bg-white rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 pb-2 border-b flex items-center gap-2">
                            <i class="fas fa-tags text-gray-500"></i>
                            Árazás
                        </h3>
                        <dl class="space-y-3">
                            @if ($product->list_price && $product->list_price > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Listaár</dt>
                                    <dd class="font-medium text-right">

                                        {{ Number::currency($product->list_price, 'HUF', 'hu', 0) }}</dd>
                                </div>
                            @endif
                            @if ($product->net_selling_price && $product->net_selling_price > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Nettó eladási ár</dt>
                                    <dd class="font-bold text-right text-blue-600">
                                        {{ Number::currency($product->net_selling_price, 'HUF', 'hu', 0) }}</dd>
                                </div>
                            @endif
                            @if ($product->gross_selling_price && $product->gross_selling_price > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Bruttó eladási ár</dt>
                                    <dd class="font-bold text-right">
                                        {{ Number::currency($product->gross_selling_price, 'HUF', 'hu', 0) }}</dd>
                                </div>
                            @endif
                            @if ($product->vat_class)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">ÁFA osztály</dt>
                                    <dd class="font-medium text-right">{{ $product->vat_class }}</dd>
                                </div>
                            @endif
                            @if ($product->list_discount && $product->list_discount > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Lista kedvezmény</dt>
                                    <dd class="font-medium text-right text-green-600">{{ $product->list_discount }}%
                                    </dd>
                                </div>
                            @endif
                            @if ($product->discount_group)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Kedvezmény csoport</dt>
                                    <dd class="font-medium text-right">{{ $product->discount_group }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Akciós</dt>
                                <dd class="font-medium text-right">
                                    @if ($product->is_on_sale)
                                        <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-sm">Igen -
                                            {{ $product->sale_percentage }}%</span>
                                    @else
                                        <span class="text-gray-500">Nem</span>
                                    @endif
                                </dd>
                            </div>
                            @if ($product->currency)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Deviza</dt>
                                    <dd class="font-medium text-right">{{ $product->currency }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Készlet és rendelés -->
                    <div class="bg-white rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 pb-2 border-b flex items-center gap-2">
                            <i class="fas fa-warehouse text-gray-500"></i>
                            Készlet és rendelés
                        </h3>
                        <dl class="space-y-3">
                            @if ($product->quantity_unit)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Mennyiségi egység</dt>
                                    <dd class="font-medium text-right">{{ $product->quantity_unit }}</dd>
                                </div>
                            @endif
                            @if ($product->secondary_unit)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Másodlagos egység</dt>
                                    <dd class="font-medium text-right">{{ $product->secondary_unit }}</dd>
                                </div>
                            @endif
                            @if ($product->minimum_stock !== null)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Minimum készlet</dt>
                                    <dd class="font-medium text-right">{{ $product->minimum_stock }}</dd>
                                </div>
                            @endif
                            @if ($product->maximum_stock !== null && $product->maximum_stock > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Maximum készlet</dt>
                                    <dd class="font-medium text-right">{{ $product->maximum_stock }}</dd>
                                </div>
                            @endif
                            @if ($product->buffer_stock !== null && $product->buffer_stock > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Puffer készlet</dt>
                                    <dd class="font-medium text-right">{{ $product->buffer_stock }}</dd>
                                </div>
                            @endif
                            @if ($product->min_order_quantity !== null && $product->min_order_quantity > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Min. rendelési menny.</dt>
                                    <dd class="font-medium text-right">{{ $product->min_order_quantity }}</dd>
                                </div>
                            @endif
                            @if ($product->order_unit !== null && $product->order_unit > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Rendelési egység</dt>
                                    <dd class="font-medium text-right">{{ $product->order_unit }}</dd>
                                </div>
                            @endif
                            @if ($product->trade_quantity !== null && $product->trade_quantity > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Kereskedelmi menny.</dt>
                                    <dd class="font-medium text-right">{{ $product->trade_quantity }}</dd>
                                </div>
                            @endif
                            @if ($product->pallet_quantity !== null && $product->pallet_quantity > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Raklap mennyiség</dt>
                                    <dd class="font-medium text-right">{{ $product->pallet_quantity }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Azonosítók és kódok -->
                    <div class="bg-white rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 pb-2 border-b flex items-center gap-2">
                            <i class="fas fa-barcode text-gray-500"></i>
                            Azonosítók és kódok
                        </h3>
                        <dl class="space-y-3">
                            @if ($product->barcode)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Vonalkód</dt>
                                    <dd class="font-medium text-right font-mono text-sm">
                                        {{ str_replace(['*', '!'], ['', ' '], $product->barcode) }}</dd>
                                </div>
                            @endif
                            @if ($product->ean_code)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">EAN kód</dt>
                                    <dd class="font-medium text-right font-mono">{{ $product->ean_code }}</dd>
                                </div>
                            @endif
                            @if ($product->ksh_prefix || $product->ksh_number)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">KSH/VTSZ szám</dt>
                                    <dd class="font-medium text-right">{{ $product->ksh_prefix }}
                                        {{ $product->ksh_number }}</dd>
                                </div>
                            @endif
                            @if ($product->supplier)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Beszállító</dt>
                                    <dd class="font-medium text-right">{{ $product->supplier }}</dd>
                                </div>
                            @endif
                            @if ($product->quality)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Minőség</dt>
                                    <dd class="font-medium text-right">{{ $product->quality }}</dd>
                                </div>
                            @endif
                            @if ($product->rating)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Minősítés</dt>
                                    <dd class="font-medium text-right">{{ $product->rating }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Leírás -->
                @if ($product->description)
                    <div class="bg-white rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 pb-2 border-b flex items-center gap-2">
                            <i class="fas fa-align-left text-gray-600"></i>
                            Leírás
                        </h3>
                        <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                    </div>
                @endif

                <!-- Megjegyzés -->
                @if ($product->short_note)
                    <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-6">
                        <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-yellow-200 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-gray-500"></i>
                            Megjegyzés
                        </h3>
                        <p class="text-gray-700">{{ $product->short_note }}</p>
                    </div>
                @endif

                <!-- Egyedi mezők -->
                @if (($product->custom_fields ?? false) && count($product->custom_fields) > 0)
                    <div class="bg-white rounded-lg border p-6">
                        <h3 class="text-lg font-semibold mb-4 pb-2 border-b flex items-center gap-2">
                            <i class="fas fa-cog text-gray-600"></i>
                            Egyedi mezők
                        </h3>
                        <dl class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($product->custom_fields as $key => $value)
                                <div class="bg-gray-50 rounded p-3">
                                    <dt class="text-gray-600 text-sm">{{ $key }}</dt>
                                    <dd class="font-medium mt-1">{{ is_array($value) ? json_encode($value) : $value }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
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
