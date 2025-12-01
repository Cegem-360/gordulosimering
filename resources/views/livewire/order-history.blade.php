<div>
    <div class="min-h-screen bg-gray-50">
        <!-- Breadcrumbs -->
        <div class="bg-white border-b">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center flex-wrap gap-2 text-sm">
                    <a href="{{ route('index') }}" class="text-blue-600 hover:underline">Kezdőlap</a>
                    <span class="text-gray-500">&gt;</span>
                    <span class="text-gray-700">Rendeléseim</span>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="max-w-3xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Rendeléseim</h1>
                    <p class="text-gray-600 mt-1">Tekintsd meg korábbi rendeléseid állapotát és részleteit</p>
                </div>

                @if ($this->orders->count() > 0)
                    <!-- Order Statistics -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ $this->orders->total() }}</p>
                                    <p class="text-xs text-gray-500">Összes rendelés</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-yellow-600"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ $this->pendingCount }}</p>
                                    <p class="text-xs text-gray-500">Folyamatban</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ $this->completedCount }}</p>
                                    <p class="text-xs text-gray-500">Teljesítve</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-wallet text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($this->totalSpent, 0, ',', ' ') }}</p>
                                    <p class="text-xs text-gray-500">Ft összesen</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders List -->
                    <div class="space-y-6">
                        @foreach ($this->orders as $order)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                                <!-- Order Header -->
                                <div class="px-6 py-4 border-b border-gray-100">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-sm">
                                                #{{ $order->id }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold text-gray-900">Rendelés #{{ $order->id }}</span>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $this->getStatusColor($order->order_status) }}">
                                                        <i class="{{ $this->getStatusIcon($order->order_status) }}"></i>
                                                        {{ $this->getStatusLabel($order->order_status) }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                                                    <span class="flex items-center gap-1">
                                                        <i class="far fa-calendar-alt"></i>
                                                        {{ $order->created_at->format('Y. m. d. H:i') }}
                                                    </span>
                                                    <span class="hidden sm:inline text-gray-300">|</span>
                                                    <span class="hidden sm:flex items-center gap-1">
                                                        <i class="far fa-credit-card"></i>
                                                        {{ $order->payment_method_title }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div class="text-right">
                                                <p class="text-sm text-gray-500">Végösszeg</p>
                                                <p class="text-xl font-bold text-gray-900">
                                                    {{ number_format($order->orderTotal() + $order->shipping_cost, 0, ',', ' ') }} Ft
                                                </p>
                                            </div>
                                            <a href="{{ route('orders.show', $order) }}"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                                <i class="fas fa-eye"></i>
                                                <span class="hidden sm:inline">Részletek</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Progress -->
                                @php
                                    $statusSteps = [
                                        'pending' => 1,
                                        'processing' => 2,
                                        'on-hold' => 2,
                                        'completed' => 4,
                                        'cancelled' => 0,
                                        'refunded' => 0,
                                        'failed' => 0,
                                    ];
                                    $currentStep = $statusSteps[$order->order_status->value] ?? 1;
                                    $isCancelled = in_array($order->order_status->value, ['cancelled', 'refunded', 'failed']);
                                @endphp
                                @if (!$isCancelled)
                                    <div class="px-6 py-3 bg-gray-50 border-b border-gray-100">
                                        <div class="flex items-center justify-between">
                                            @foreach ([['icon' => 'fa-clipboard-check', 'label' => 'Megrendelve'], ['icon' => 'fa-cog', 'label' => 'Feldolgozás'], ['icon' => 'fa-truck', 'label' => 'Szállítás'], ['icon' => 'fa-check-circle', 'label' => 'Kézbesítve']] as $index => $step)
                                                <div class="flex flex-col items-center {{ $index < 3 ? 'flex-1' : '' }}">
                                                    <div class="flex items-center w-full">
                                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $currentStep > $index ? 'bg-green-500 text-white' : ($currentStep == $index + 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400') }}">
                                                            <i class="fas {{ $step['icon'] }} text-xs"></i>
                                                        </div>
                                                        @if ($index < 3)
                                                            <div class="flex-1 h-1 mx-2 {{ $currentStep > $index + 1 ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs text-gray-500 mt-1 hidden sm:block">{{ $step['label'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Order Items -->
                                <div class="px-6 py-4">
                                    <div class="space-y-3">
                                        @foreach ($order->orderItems->take(3) as $item)
                                            <div class="flex items-center gap-4 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                                <div class="w-16 h-16 bg-gray-100 rounded-lg shrink-0 overflow-hidden border border-gray-200">
                                                    @if ($item->product && $item->product->images)
                                                        <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}"
                                                            class="w-full h-full object-contain">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center">
                                                            <i class="fas fa-box text-gray-300 text-xl"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    @if ($item->product)
                                                        <a href="{{ route('products.show', $item->product) }}"
                                                            class="text-sm font-medium text-gray-900 hover:text-blue-600 truncate block">
                                                            {{ $item->product->name }}
                                                        </a>
                                                        <p class="text-xs text-gray-400 font-mono">{{ $item->product->product_code }}</p>
                                                    @else
                                                        <p class="text-sm font-medium text-gray-500">Törölt termék</p>
                                                    @endif
                                                    <p class="text-sm text-gray-500 mt-0.5">
                                                        {{ $item->quantity }} db × {{ number_format($item->total, 0, ',', ' ') }} Ft
                                                    </p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <p class="text-sm font-semibold text-gray-900">
                                                        {{ number_format($item->quantity * $item->total, 0, ',', ' ') }} Ft
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach

                                        @if ($order->orderItems->count() > 3)
                                            <div class="text-center py-2">
                                                <span class="text-sm text-gray-500">
                                                    + további {{ $order->orderItems->count() - 3 }} termék
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Order Footer -->
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div class="flex flex-wrap items-center gap-4 text-sm">
                                            <div class="flex items-center gap-2 text-gray-600">
                                                <i class="fas fa-truck text-gray-400"></i>
                                                <span>{{ $order->shippingMethod?->name ?? 'Ismeretlen' }}</span>
                                                @if ($order->shipping_cost > 0)
                                                    <span class="text-gray-400">({{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft)</span>
                                                @else
                                                    <span class="text-green-600 font-medium">Ingyenes</span>
                                                @endif
                                            </div>
                                            @if ($order->shipping_tracking_number)
                                                <div class="flex items-center gap-2 text-gray-600">
                                                    <i class="fas fa-barcode text-gray-400"></i>
                                                    <span class="font-mono text-xs bg-gray-200 px-2 py-0.5 rounded">{{ $order->shipping_tracking_number }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <div class="text-xs text-gray-500">
                                                    <span>Termékek: {{ number_format($order->orderTotal(), 0, ',', ' ') }} Ft</span>
                                                    @if ($order->shipping_cost > 0)
                                                        <span class="mx-1">+</span>
                                                        <span>Szállítás: {{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shipping Address (Collapsible) -->
                                    <div x-data="{ showDetails: false }" class="mt-4">
                                        <button @click="showDetails = !showDetails"
                                            class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': showDetails }"></i>
                                            Szállítási adatok
                                        </button>
                                        <div x-show="showDetails" x-collapse class="mt-3 p-4 bg-white rounded-lg border border-gray-200">
                                            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-1">Szállítási cím</p>
                                                    <p class="text-gray-600">{{ $order->shipping_name }}</p>
                                                    <p class="text-gray-600">{{ $order->shipping_postcode }} {{ $order->shipping_city }}</p>
                                                    <p class="text-gray-600">{{ $order->shipping_address_1 }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-1">Számlázási cím</p>
                                                    <p class="text-gray-600">{{ $order->billing_name }}</p>
                                                    <p class="text-gray-600">{{ $order->billing_postcode }} {{ $order->billing_city }}</p>
                                                    <p class="text-gray-600">{{ $order->billing_address_1 }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8">
                        {{ $this->orders->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-shopping-bag text-gray-300 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Még nincs rendelésed</h3>
                        <p class="text-gray-600 mb-6 max-w-md mx-auto">
                            Böngéssz termékeink között és találd meg a számodra megfelelő csapágyakat és alkatrészeket!
                        </p>
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                            <i class="fas fa-search mr-2"></i>
                            Termékek böngészése
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
