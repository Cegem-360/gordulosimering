<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumbs -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center space-x-2 text-sm">
                <a href="/" class="text-blue-600 hover:underline">Kezdőlap</a>
                <span class="text-gray-500">&gt;</span>
                <span class="text-gray-700">Rendelés visszaigazolás</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        @if ($order)
            <!-- Success Message -->
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg border-2 border-green-200 p-8 text-center mb-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-4xl text-green-600"></i>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Köszönjük a rendelését!</h1>
                    <p class="text-gray-600 mb-4">A rendelését sikeresen rögzítettük.</p>
                    <p class="text-lg font-semibold text-blue-600">
                        Rendelésszám: #{{ $order->id }}
                    </p>
                </div>

                <!-- Order Details -->
                <div class="bg-white rounded-lg border p-6 shadow-sm mb-6">
                    <h2 class="text-xl font-bold mb-6 pb-4 border-b flex items-center gap-2">
                        <i class="fas fa-file-invoice text-gray-500"></i>
                        Rendelés részletei
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Billing Address -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Számlázási cím</h3>
                            <div class="text-gray-600 text-sm space-y-1">
                                <p class="font-medium text-gray-900">{{ $order->billing_name }}</p>
                                @if ($order->billing_company_name)
                                    <p>{{ $order->billing_company_name }}</p>
                                @endif
                                <p>{{ $order->billing_postcode }} {{ $order->billing_city }}</p>
                                <p>{{ $order->billing_address_1 }}</p>
                                @if ($order->billing_address_2)
                                    <p>{{ $order->billing_address_2 }}</p>
                                @endif
                                <p>{{ $order->billing_country }}</p>
                                <p class="pt-2">{{ $order->billing_email }}</p>
                                <p>{{ $order->billing_phone }}</p>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Szállítási cím</h3>
                            <div class="text-gray-600 text-sm space-y-1">
                                <p class="font-medium text-gray-900">{{ $order->shipping_name }}</p>
                                <p>{{ $order->shipping_postcode }} {{ $order->shipping_city }}</p>
                                <p>{{ $order->shipping_address_1 }}</p>
                                @if ($order->shipping_address_2)
                                    <p>{{ $order->shipping_address_2 }}</p>
                                @endif
                                <p>{{ $order->shipping_country }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Shipping Method -->
                    <div class="grid md:grid-cols-2 gap-6 mt-6 pt-6 border-t">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Fizetési mód</h3>
                            <p class="text-gray-600">{{ $order->payment_method_title }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Szállítási mód</h3>
                            <p class="text-gray-600">{{ $order->shippingMethod?->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions (for bank transfer) -->
                @if ($order->payment_method === 'bacs')
                    <div class="bg-blue-50 rounded-lg border border-blue-200 p-6 mb-6">
                        <h3 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-university"></i>
                            Banki átutalás információ
                        </h3>
                        <p class="text-blue-800 text-sm mb-4">
                            Kérjük, utalja át a rendelés összegét az alábbi bankszámlaszámra. A közlemény rovatban
                            tüntesse fel a rendelésszámot: <strong>#{{ $order->id }}</strong>
                        </p>
                        <div class="bg-white rounded p-4 text-sm">
                            <p class="text-gray-600">A pontos átutalási adatokat e-mailben is elküldjük.</p>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold transition-colors">
                        <i class="fas fa-shopping-bag"></i>
                        Tovább vásárolok
                    </a>
                    <a href="{{ route('index') }}"
                        class="inline-flex items-center justify-center gap-2 border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                        <i class="fas fa-home"></i>
                        Vissza a kezdőlapra
                    </a>
                </div>
            </div>
        @else
            <!-- No Order Found -->
            <div class="bg-white rounded-lg border p-12 text-center max-w-xl mx-auto">
                <i class="fas fa-exclamation-circle text-6xl text-gray-300 mb-6"></i>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">Nincs megjeleníthető rendelés</h2>
                <p class="text-gray-500 mb-6">A keresett rendelés nem található vagy lejárt a munkamenet.</p>
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold transition-colors">
                    <i class="fas fa-shopping-bag"></i>
                    Termékek böngészése
                </a>
            </div>
        @endif
    </div>
</div>
