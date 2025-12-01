<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumbs -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center space-x-2 text-sm">
                <a href="/" class="text-blue-600 hover:underline">Kezdőlap</a>
                <span class="text-gray-500">&gt;</span>
                <a href="{{ route('cart') }}" class="text-blue-600 hover:underline">Kosár</a>
                <span class="text-gray-500">&gt;</span>
                <span class="text-gray-700">Pénztár</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="flex items-center gap-3 mb-8">
            <i class="fas fa-credit-card text-3xl text-gray-400"></i>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Pénztár</h1>
        </div>

        <form wire:submit="create">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column: Billing & Shipping Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                        {{ $this->form }}
                    </div>

                    <!-- Ship to Different Address -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="shipToDifferentAddress"
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-900">Szállítás másik címre?</span>
                        </label>

                        @if ($shipToDifferentAddress)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-base font-semibold text-gray-900 mb-4">Szállítási cím</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label for="shipping_name" class="block text-sm font-medium text-gray-700 mb-1">Címzett neve</label>
                                        <input type="text" id="shipping_name" wire:model="data.shipping_name"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                                    </div>
                                    <div>
                                        <label for="shipping_postcode" class="block text-sm font-medium text-gray-700 mb-1">Irányítószám</label>
                                        <input type="text" id="shipping_postcode" wire:model="data.shipping_postcode"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                                    </div>
                                    <div>
                                        <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-1">Város</label>
                                        <input type="text" id="shipping_city" wire:model="data.shipping_city"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="shipping_address_1" class="block text-sm font-medium text-gray-700 mb-1">Utca, házszám</label>
                                        <input type="text" id="shipping_address_1" wire:model="data.shipping_address_1"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="shipping_address_2" class="block text-sm font-medium text-gray-700 mb-1">Emelet, ajtó (opcionális)</label>
                                        <input type="text" id="shipping_address_2" wire:model="data.shipping_address_2"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                                    </div>
                                    <div>
                                        <label for="shipping_country" class="block text-sm font-medium text-gray-700 mb-1">Ország</label>
                                        <input type="text" id="shipping_country" wire:model="data.shipping_country" value="Magyarország"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                                    </div>
                                    <div>
                                        <label for="shipping_state" class="block text-sm font-medium text-gray-700 mb-1">Megye</label>
                                        <input type="text" id="shipping_state" wire:model="data.shipping_state"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Back Button (mobile) -->
                    <div class="lg:hidden">
                        <a href="{{ route('cart') }}"
                            class="w-full px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 inline-flex items-center justify-center gap-2 font-medium transition-colors">
                            <i class="fas fa-arrow-left"></i>
                            Vissza a kosárhoz
                        </a>
                    </div>
                </div>

                <!-- Right Column: Order Summary + Shipping + Payment -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Order Summary -->
                    <div class="bg-white rounded-lg border-2 border-blue-100 p-6 shadow-sm">
                        <h2 class="text-lg font-bold mb-4 pb-3 border-b uppercase tracking-wide">
                            A rendelés tartalma
                        </h2>

                        @if (count($cartItems) > 0)
                            <!-- Cart Items -->
                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between text-sm font-medium text-gray-500 uppercase">
                                    <span>Termék</span>
                                    <span>Részösszeg</span>
                                </div>
                                @foreach ($cartItems as $item)
                                    <div wire:key="summary-item-{{ $item->product_id }}"
                                        class="flex justify-between items-start text-sm py-2 border-b border-gray-100">
                                        <div class="flex-1">
                                            <p class="text-gray-900 line-clamp-2">{{ $item->product->name }}
                                                <span class="text-gray-500">× {{ $item->quantity }}</span>
                                            </p>
                                        </div>
                                        <p class="font-medium text-gray-900 ml-2 whitespace-nowrap">
                                            {{ number_format($item->product->net_selling_price * $item->quantity, 0, ',', ' ') }}
                                            Ft
                                        </p>
                                    </div>
                                @endforeach
                            </div>

                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <dt class="text-gray-600">Részösszeg</dt>
                                    <dd class="font-medium">{{ number_format($this->subtotal, 0, ',', ' ') }} Ft</dd>
                                </div>

                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <dt class="text-gray-600">Szállítás</dt>
                                    <dd class="font-medium">
                                        @if ($this->selectedShipping)
                                            {{ $this->selectedShipping->title }}
                                            @if ($this->shippingCost > 0)
                                                - {{ number_format($this->shippingCost, 0, ',', ' ') }} Ft
                                            @else
                                                - <span class="text-green-600">Ingyenes</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Válasszon szállítási módot</span>
                                        @endif
                                    </dd>
                                </div>

                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <dt class="text-gray-500">ÁFA (27%)</dt>
                                    <dd class="text-gray-500">{{ number_format($this->vatAmount, 0, ',', ' ') }} Ft</dd>
                                </div>

                                <div class="flex justify-between py-3 text-base">
                                    <dt class="font-bold">Összeg</dt>
                                    <dd class="font-bold text-xl">
                                        {{ number_format($this->total, 0, ',', ' ') }} Ft
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">A kosár üres</p>
                            </div>
                        @endif
                    </div>

                    <!-- Shipping Method Selection -->
                    @if ($this->shippingMethods->count() > 0)
                        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                            <h3 class="text-sm font-bold mb-3 uppercase tracking-wide text-gray-700">Szállítási mód</h3>
                            <div class="space-y-2">
                                @foreach ($this->shippingMethods as $method)
                                    <label wire:key="shipping-{{ $method->id }}"
                                        class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all {{ $selectedShippingMethod == $method->id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" name="shipping_method"
                                            wire:model.live="selectedShippingMethod" value="{{ $method->id }}"
                                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm font-medium text-gray-900">{{ $method->title }}</span>
                                            @if ($method->description)
                                                <p class="text-xs text-gray-500 truncate">{{ $method->description }}</p>
                                            @endif
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                                            @if ($method->cost > 0)
                                                {{ number_format($method->cost, 0, ',', ' ') }} Ft
                                            @else
                                                Ingyenes
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedShippingMethod')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Payment Method Selection -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                        <h3 class="text-sm font-bold mb-3 uppercase tracking-wide text-gray-700">Fizetési mód</h3>
                        <div class="space-y-2">
                            @foreach ($this->paymentMethods as $key => $method)
                                <label wire:key="payment-{{ $key }}"
                                    class="block p-3 border rounded-lg cursor-pointer transition-all {{ $selectedPaymentMethod == $key ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="payment_method"
                                            wire:model.live="selectedPaymentMethod" value="{{ $key }}"
                                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-900">{{ $method['title'] }}</span>
                                    </div>
                                    @if ($selectedPaymentMethod == $key)
                                        <p class="text-xs text-gray-500 mt-2 ml-7">{{ $method['description'] }}</p>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('selectedPaymentMethod')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Terms Acceptance -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="acceptTerms"
                                class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-700">
                                Elolvastam és elfogadom az
                                <a href="{{ route('terms-and-conditions') }}" target="_blank"
                                    class="text-blue-600 hover:underline font-medium">Általános Szerződési
                                    Feltételek</a>-ben foglaltakat *
                            </span>
                        </label>
                        @error('acceptTerms')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Registration for guests / Save data for logged in users -->
                    @guest
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="createAccount"
                                    class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700">
                                    Szeretnék fiókot létrehozni
                                </span>
                            </label>

                            @if ($createAccount)
                                <p class="mt-3 text-xs text-gray-500">
                                    A rendelés leadása után emailben küldünk egy linket, ahol beállíthatod a jelszavadat.
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="saveDataForFuture"
                                    class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700">
                                    Adatok mentése a jövőbeli rendelésekhez
                                </span>
                            </label>
                        </div>
                    @endguest

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full bg-gray-800 text-white py-4 rounded-lg hover:bg-gray-900 font-bold text-lg uppercase tracking-wide transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        @if (!$acceptTerms) disabled @endif>
                        Megrendelés
                    </button>

                    <!-- Privacy Note -->
                    <p class="text-xs text-gray-500 text-center">
                        A személyes adatokat a rendelés feldolgozásához, a weboldalon történő vásárlási élmény
                        fenntartásához és más célokra használjuk, melyeket az
                        <a href="{{ route('privacy-policy') }}" class="text-blue-600 hover:underline">Adatkezelési
                            tájékoztató</a> tartalmaz.
                    </p>

                    <!-- Back Button (desktop) -->
                    <div class="hidden lg:block text-center">
                        <a href="{{ route('cart') }}" class="text-blue-600 hover:underline text-sm inline-flex items-center gap-1">
                            <i class="fas fa-arrow-left text-xs"></i>
                            Vissza a kosárhoz
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <x-filament-actions::modals />
</div>
