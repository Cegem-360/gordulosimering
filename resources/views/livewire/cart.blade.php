<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumbs -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center space-x-2 text-sm">
                <a href="/" class="text-blue-600 hover:underline">Kezdőlap</a>
                <span class="text-gray-500">&gt;</span>
                <span class="text-gray-700">Kosár</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="flex items-center gap-3 mb-8">
            <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Kosár</h1>
            @if (count($cartItems) > 0)
                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                    {{ $this->itemCount }} termék
                </span>
            @endif
        </div>

        @if (count($cartItems) > 0)
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach ($cartItems as $index => $item)
                        <div class="bg-white rounded-lg border p-4 md:p-6" wire:key="cart-item-{{ $item['id'] }}">
                            <div class="flex flex-col md:flex-row gap-4">
                                <!-- Product Image -->
                                <a href="{{ route('products.show', $item['slug']) }}"
                                    class="shrink-0 w-full md:w-32 h-32 bg-gray-100 rounded-lg overflow-hidden">
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                            class="w-full h-full object-contain">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-box text-4xl text-gray-300"></i>
                                        </div>
                                    @endif
                                </a>

                                <!-- Product Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col md:flex-row md:justify-between gap-2">
                                        <div>
                                            <a href="{{ route('products.show', $item['slug']) }}"
                                                class="text-lg font-semibold text-blue-600 hover:underline line-clamp-2">
                                                {{ $item['name'] }}
                                            </a>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Cikkszám: <span class="font-mono">{{ $item['product_code'] }}</span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            @if ($item['in_stock'])
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
                                    </div>

                                    <!-- Price and Quantity Row -->
                                    <div class="flex flex-col md:flex-row md:items-end justify-between mt-4 gap-4">
                                        <!-- Quantity Selector -->
                                        <div x-data="{ quantity: @entangle('cartItems.' . $index . '.quantity'), min: {{ $item['min_order_quantity'] }} }">
                                            <label class="block text-sm text-gray-600 mb-1">Mennyiség
                                                ({{ $item['quantity_unit'] }})
                                            </label>
                                            <div class="flex items-center gap-1">
                                                <button type="button"
                                                    @click="quantity = Math.max(min, quantity - 1); $wire.updateQuantity({{ $index }}, quantity)"
                                                    class="w-10 h-10 rounded-l-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <input type="number" x-model="quantity" :min="min"
                                                    @change="$wire.updateQuantity({{ $index }}, quantity)"
                                                    class="w-16 h-10 text-center font-semibold border-y border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                                <button type="button"
                                                    @click="quantity++; $wire.updateQuantity({{ $index }}, quantity)"
                                                    class="w-10 h-10 rounded-r-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold flex items-center justify-center transition-colors border border-gray-300 cursor-pointer">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Price -->
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">Egységár (nettó)</p>
                                            <p class="text-lg font-semibold">
                                                {{ number_format($item['net_price'], 0, ',', ' ') }} Ft
                                            </p>
                                            <p class="text-xl font-bold text-blue-600 mt-1">
                                                {{ number_format($item['net_price'] * $item['quantity'], 0, ',', ' ') }}
                                                Ft
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="mt-4 pt-4 border-t flex justify-end">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="text-red-600 hover:text-red-700 text-sm inline-flex items-center gap-1 cursor-pointer">
                                            <i class="fas fa-trash-alt"></i>
                                            Eltávolítás
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Continue Shopping -->
                    <div class="flex justify-start">
                        <a href="{{ route('products.index') }}"
                            class="text-blue-600 hover:underline inline-flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i>
                            Vásárlás folytatása
                        </a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg border-2 border-blue-100 p-6 shadow-sm sticky top-4">
                        <h2 class="text-xl font-bold mb-6 pb-4 border-b flex items-center gap-2">
                            <i class="fas fa-receipt text-gray-500"></i>
                            Összesítés
                        </h2>

                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Termékek ({{ $this->itemCount }} db)</dt>
                                <dd class="font-medium">{{ number_format($this->subtotal, 0, ',', ' ') }} Ft</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Nettó összesen</dt>
                                <dd class="font-semibold">{{ number_format($this->subtotal, 0, ',', ' ') }} Ft</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">ÁFA (27%)</dt>
                                <dd class="text-gray-500">{{ number_format($this->vatAmount, 0, ',', ' ') }} Ft</dd>
                            </div>
                            <div class="flex justify-between pt-3 border-t">
                                <dt class="text-lg font-bold">Bruttó összesen</dt>
                                <dd class="text-2xl font-bold text-blue-600">
                                    {{ number_format($this->total, 0, ',', ' ') }} Ft</dd>
                            </div>
                        </dl>

                        <!-- Checkout Button -->
                        <button type="button"
                            class="w-full mt-6 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 font-semibold text-lg transition-colors shadow-sm">
                            <i class="fas fa-lock"></i>
                            Tovább a pénztárhoz
                        </button>

                        <!-- Payment Methods -->
                        {{-- <div class="mt-6 pt-4 border-t">
                            <p class="text-xs text-gray-500 text-center mb-3">Elfogadott fizetési módok</p>
                            <div class="flex justify-center gap-3 text-gray-400">
                                ...
                            </div>
                        </div> --}}

                        <!-- Security Note -->
                        {{-- <p class="mt-4 text-xs text-gray-500 text-center flex items-center justify-center gap-1">
                            <i class="fas fa-shield-alt text-green-600"></i>
                            Biztonságos vásárlás SSL titkosítással
                        </p> --}}
                    </div>
                </div>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="bg-white rounded-lg border p-12 text-center">
                <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-6"></i>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">A kosár üres</h2>
                <p class="text-gray-500 mb-6">Még nem adott hozzá termékeket a kosárhoz.</p>
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    Termékek böngészése
                </a>
            </div>
        @endif
    </div>
</div>
