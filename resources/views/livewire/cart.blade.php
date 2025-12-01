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
                    @foreach ($cartItems as $item)
                        <livewire:cart-item :product-id="$item->product_id" :quantity="$item->quantity" :key="'cart-item-' . $item->product_id" />
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
