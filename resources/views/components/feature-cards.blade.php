<div class="bg-white py-12 lg:py-16">
    <div class="">
        <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
            <!-- Webshop Card -->
            <a href="{{ route('sale') }}" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img src="{{ Vite::asset('resources/images/webshop-banner.webp') }}" alt="Webáruház – jelentős kedvezmények"
                        class="w-full h-64 object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            Webáruház – jelentős kedvezmények
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                    </div>
                </div>
            </a>

            <!-- 24/7 Service Card -->
            <a href="{{ route('services') }}#ugyelet" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img src="{{ Vite::asset('resources/images/24-7-service.webp') }}" alt="24 órás ügyelet"
                        class="w-full h-64 object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            24 órás ügyelet, készenlét
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                        <p class="text-white/80 text-sm mt-1">Üzletnyitás éjjel-nappal</p>
                    </div>
                </div>
            </a>

            <!-- Stores Card -->
            <a href="{{ route('contact') }}" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img src="{{ Vite::asset('resources/images/stores/korosi-csoma.webp') }}" alt="Üzleteink"
                        class="w-full h-64 object-cover object-[center_30%]">
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            Üzleteink
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                        <p class="text-white/80 text-sm mt-1">2 helyszín Budapesten</p>
                    </div>
                </div>
            </a>
            <!-- Brands Card -->
            <a href="{{ route('brands') }}" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 bg-white border border-gray-200 h-64">
                    <div class="grid grid-cols-3 gap-x-4 gap-y-5 items-center justify-items-center px-6 pt-8">
                        @foreach (collect(config('brands'))->take(6) as $brand)
                            <img src="{{ Vite::asset('resources/images/brands/' . $brand['logo']) }}" alt="{{ $brand['name'] }}"
                                @class(['max-h-9 w-auto max-w-full', 'invert' => $brand['invert'] ?? false])>
                        @endforeach
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            Márkák
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                    </div>
                </div>
            </a>

            <!-- Catalogues Card -->
            <a href="{{ route('catalogs') }}" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img src="{{ Vite::asset('resources/images/stainless-steel-thrust-ball-bearing-and-linear-bea-2025-01-29-04-41-07-utc.webp') }}" alt="Katalógusok"
                        class="w-full h-64 object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            Katalógusok
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                        <p class="text-white/80 text-sm mt-1">Letölthető termékkatalógusok</p>
                    </div>
                </div>
            </a>

            <!-- Shipping Costs Card -->
            <a href="{{ route('services') }}#hazhozszallitas" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 bg-linear-to-br from-blue-900 to-blue-600 h-64">
                    <i class="fas fa-truck-fast absolute top-10 left-1/2 -translate-x-1/2 text-white/25 text-8xl"></i>
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/50 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            Szállítási költségek
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                        <p class="text-white/80 text-sm mt-1">Házhozszállítás és GLS díjak</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
