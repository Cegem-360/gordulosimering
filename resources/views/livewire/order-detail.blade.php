<div>
    <div class="min-h-screen bg-gray-50">
        <!-- Breadcrumbs -->
        <div class="bg-white border-b">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center flex-wrap gap-2 text-sm">
                    <a href="{{ route('index') }}" class="text-blue-600 hover:underline">Kezdőlap</a>
                    <span class="text-gray-500">&gt;</span>
                    <a href="{{ route('orders.history') }}" class="text-blue-600 hover:underline">Rendeléseim</a>
                    <span class="text-gray-500">&gt;</span>
                    <span class="text-gray-700">Rendelés #{{ $order->id }}</span>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Back Button -->
            <div class="max-w-4xl mx-auto mb-6">
                <a href="{{ route('orders.history') }}"
                    class="inline-flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Vissza a rendelésekhez
                </a>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="grid lg:grid-cols-[1fr_280px] gap-6">
                    <!-- Left Column - Main Content -->
                    <div class="space-y-6">
                        <!-- Order Header Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-sm">
                                            #{{ $order->id }}
                                        </div>
                                        <div>
                                            <h1 class="text-2xl font-bold text-gray-900">Rendelés #{{ $order->id }}</h1>
                                            <p class="text-gray-500 flex items-center gap-2 mt-1">
                                                <i class="far fa-calendar-alt"></i>
                                                {{ $order->created_at->format('Y. m. d. H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-start sm:items-end gap-2">
                                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium border {{ $this->getStatusColor($order->order_status) }}">
                                            <i class="{{ $this->getStatusIcon($order->order_status) }}"></i>
                                            {{ $this->getStatusLabel($order->order_status) }}
                                        </span>
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
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                    <div class="flex items-center justify-between">
                                        @foreach ([['icon' => 'fa-clipboard-check', 'label' => 'Megrendelve'], ['icon' => 'fa-cog', 'label' => 'Feldolgozás'], ['icon' => 'fa-truck', 'label' => 'Szállítás'], ['icon' => 'fa-check-circle', 'label' => 'Kézbesítve']] as $index => $step)
                                            <div class="flex flex-col items-center {{ $index < 3 ? 'flex-1' : '' }}">
                                                <div class="flex items-center w-full">
                                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $currentStep > $index ? 'bg-green-500 text-white' : ($currentStep == $index + 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400') }}">
                                                        <i class="fas {{ $step['icon'] }}"></i>
                                                    </div>
                                                    @if ($index < 3)
                                                        <div class="flex-1 h-1 mx-2 {{ $currentStep > $index + 1 ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-gray-500 mt-2 text-center">{{ $step['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Order Items -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-box text-gray-400"></i>
                                    Rendelt termékek
                                </h2>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach ($order->orderItems as $item)
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center gap-4">
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
                                                        class="text-sm font-medium text-gray-900 hover:text-blue-600 block truncate">
                                                        {{ $item->product->name }}
                                                    </a>
                                                    <p class="text-xs text-gray-400 font-mono">{{ $item->product->product_code }}</p>
                                                @else
                                                    <p class="text-sm font-medium text-gray-500">Törölt termék</p>
                                                @endif
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ $item->quantity }} db × {{ number_format($item->total, 0, ',', ' ') }} Ft
                                                </p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="text-base font-bold text-gray-900">
                                                    {{ number_format($item->quantity * $item->total, 0, ',', ' ') }} Ft
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Shipping & Billing Info -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <!-- Shipping Address -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                        Szállítási cím
                                    </h2>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-1 text-sm text-gray-600">
                                        <p class="font-medium text-gray-900">{{ $order->shipping_name }}</p>
                                        <p>{{ $order->shipping_postcode }} {{ $order->shipping_city }}</p>
                                        <p>{{ $order->shipping_address_1 }}</p>
                                        @if ($order->shipping_address_2)
                                            <p>{{ $order->shipping_address_2 }}</p>
                                        @endif
                                        <p>{{ $order->shipping_country }}</p>
                                    </div>

                                    @if ($order->shipping_tracking_number)
                                        <div class="mt-4 pt-4 border-t border-gray-100">
                                            <p class="text-xs text-gray-500 mb-1">Csomagkövetési szám:</p>
                                            <p class="font-mono text-xs bg-gray-100 px-2 py-1 rounded inline-block">
                                                {{ $order->shipping_tracking_number }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Billing Address -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                        <i class="fas fa-file-invoice text-gray-400"></i>
                                        Számlázási cím
                                    </h2>
                                </div>
                                <div class="p-5">
                                    <div class="space-y-1 text-sm text-gray-600">
                                        <p class="font-medium text-gray-900">{{ $order->billing_name }}</p>
                                        @if ($order->billing_company_name)
                                            <p class="text-gray-500">{{ $order->billing_company_name }}</p>
                                        @endif
                                        <p>{{ $order->billing_postcode }} {{ $order->billing_city }}</p>
                                        <p>{{ $order->billing_address_1 }}</p>
                                        @if ($order->billing_address_2)
                                            <p>{{ $order->billing_address_2 }}</p>
                                        @endif
                                        <p>{{ $order->billing_country }}</p>
                                    </div>

                                    @if ($order->billing_vat_number)
                                        <div class="mt-4 pt-4 border-t border-gray-100">
                                            <p class="text-xs text-gray-500">Adószám:</p>
                                            <p class="font-medium text-sm">{{ $order->billing_vat_number }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-address-book text-gray-400"></i>
                                    Kapcsolattartási adatok
                                </h2>
                            </div>
                            <div class="p-5">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                                            <i class="fas fa-envelope text-gray-500 text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-500">E-mail</p>
                                            <a href="mailto:{{ $order->billing_email }}" class="text-sm text-blue-600 hover:underline truncate block">
                                                {{ $order->billing_email }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                                            <i class="fas fa-phone text-gray-500 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Telefon</p>
                                            <a href="tel:{{ $order->billing_phone }}" class="text-sm text-blue-600 hover:underline">
                                                {{ $order->billing_phone }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Sticky Sidebar -->
                    <div class="lg:sticky lg:top-4 space-y-4 h-fit">
                        <!-- Order Summary -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h2 class="font-semibold text-gray-900">Összesítés</h2>
                            </div>
                            <div class="p-5">
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Termékek:</span>
                                        <span class="font-medium text-gray-900">{{ number_format($order->orderTotal(), 0, ',', ' ') }} Ft</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Szállítás:</span>
                                        @if ($order->shipping_cost > 0)
                                            <span class="font-medium text-gray-900">{{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft</span>
                                        @else
                                            <span class="font-medium text-green-600">Ingyenes</span>
                                        @endif
                                    </div>
                                    <div class="pt-3 border-t border-gray-200">
                                        <div class="flex justify-between">
                                            <span class="font-semibold text-gray-900">Végösszeg:</span>
                                            <span class="text-xl font-bold text-blue-600">{{ number_format($order->orderTotal() + $order->shipping_cost, 0, ',', ' ') }} Ft</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-credit-card text-gray-400"></i>
                                    Fizetési mód
                                </h2>
                            </div>
                            <div class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                                        @if ($order->payment_method === 'bacs')
                                            <i class="fas fa-university text-blue-600"></i>
                                        @elseif ($order->payment_method === 'cod')
                                            <i class="fas fa-money-bill-wave text-blue-600"></i>
                                        @else
                                            <i class="fas fa-credit-card text-blue-600"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $order->payment_method_title }}</p>
                                        <p class="text-xs mt-0.5">
                                            @if ($order->set_paid)
                                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Fizetve</span>
                                            @else
                                                <span class="text-yellow-600"><i class="fas fa-clock mr-1"></i>Fizetésre vár</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Method -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="fas fa-truck text-gray-400"></i>
                                    Szállítási mód
                                </h2>
                            </div>
                            <div class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                                        <i class="fas fa-truck text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $order->shippingMethod?->name ?? 'Ismeretlen' }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            @if ($order->shipping_cost > 0)
                                                {{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft
                                            @else
                                                <span class="text-green-600">Ingyenes szállítás</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
