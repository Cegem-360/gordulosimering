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
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Rendeléseim</h1>

                @if ($this->orders->count() > 0)
                    <div class="space-y-4">
                        @foreach ($this->orders as $order)
                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <!-- Order Header -->
                                <div class="bg-gray-50 px-6 py-4 border-b">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div>
                                            <span class="text-sm text-gray-500">Rendelés azonosító:</span>
                                            <span class="font-semibold text-gray-900">#{{ $order->id }}</span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <span class="text-sm text-gray-500">
                                                {{ $order->created_at->format('Y. m. d.') }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusColor($order->order_status) }}">
                                                {{ $this->getStatusLabel($order->order_status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="px-6 py-4">
                                    <div class="space-y-3">
                                        @foreach ($order->orderItems as $item)
                                            <div class="flex items-center gap-4">
                                                <div class="w-16 h-16 bg-gray-100 rounded shrink-0 overflow-hidden">
                                                    @if ($item->product && $item->product->images)
                                                        <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}"
                                                            class="w-full h-full object-contain">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center">
                                                            <i class="fas fa-box text-gray-300"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $item->product?->name ?? 'Törölt termék' }}
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $item->quantity }} db x {{ number_format($item->total, 0, ',', ' ') }} Ft
                                                    </p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <p class="text-sm font-semibold text-gray-900">
                                                        {{ number_format($item->quantity * $item->total, 0, ',', ' ') }} Ft
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Order Footer -->
                                <div class="bg-gray-50 px-6 py-4 border-t">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div class="text-sm text-gray-500">
                                            <i class="fas fa-truck mr-1"></i>
                                            {{ $order->shippingMethod?->name ?? 'Ismeretlen szállítás' }}
                                            @if ($order->shipping_cost > 0)
                                                ({{ number_format($order->shipping_cost, 0, ',', ' ') }} Ft)
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm text-gray-500">Összesen:</span>
                                            <span class="text-lg font-bold text-gray-900 ml-2">
                                                {{ number_format($order->orderTotal() + $order->shipping_cost, 0, ',', ' ') }} Ft
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $this->orders->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <i class="fas fa-shopping-bag text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Még nincs rendelésed</h3>
                        <p class="text-gray-600 mb-4">Böngéssz termékeink között és add le első rendelésedet!</p>
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Termékek böngészése
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
