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
                    {{-- 30 logos fill the box; the last row sits behind the title shading, as the photo
                         tiles' pictures do. MEGADYNE ('tile' => false in config/brands.php) is left out. --}}
                    <div class="absolute inset-0 grid grid-cols-5 grid-rows-6 gap-x-2 gap-y-1 items-center justify-items-center px-3 py-2">
                        @foreach (collect(config('brands'))->reject(fn (array $brand): bool => ($brand['tile'] ?? true) === false)->take(30) as $brand)
                            <img src="{{ Vite::asset('resources/images/brands/' . $brand['logo']) }}" alt="{{ $brand['name'] }}"
                                @class(['w-auto max-w-full object-contain', 'h-7' => ($brand['size'] ?? null) === 'box', 'h-5' => ($brand['size'] ?? null) !== 'box'])>
                        @endforeach
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/75 via-black/30 to-transparent pt-10 px-6 pb-5">
                        <h3 class="text-white text-lg font-semibold flex items-center gap-2 whitespace-nowrap">
                            Forgalmazott márkáink
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
                    {{-- A shelf of catalogue spines in different colours and sizes, some with a brand
                         logo, drawn here because no neutral bookshelf photo was at hand (client, 2026-10-13). --}}
                    @php
                        $spines = [
                            ['bg' => '#34445e', 'h' => 74, 'w' => 20],
                            ['bg' => '#eee9df', 'h' => 92, 'w' => 30, 'logo' => 'skf.svg'],
                            ['bg' => '#9b524b', 'h' => 68, 'w' => 16],
                            ['bg' => '#4a6458', 'h' => 82, 'w' => 22],
                            ['bg' => '#eee9df', 'h' => 88, 'w' => 28, 'logo' => 'fag.webp'],
                            ['bg' => '#b59a5a', 'h' => 64, 'w' => 18],
                            ['bg' => '#eee9df', 'h' => 94, 'w' => 28, 'logo' => 'nsk.svg'],
                            ['bg' => '#6f5048', 'h' => 72, 'w' => 20],
                            ['bg' => '#466178', 'h' => 84, 'w' => 22],
                            ['bg' => '#eee9df', 'h' => 90, 'w' => 28, 'logo' => 'koyo.webp'],
                            ['bg' => '#626a4c', 'h' => 70, 'w' => 16],
                        ];
                    @endphp
                    <div class="h-64 bg-linear-to-b from-[#e9e1d3] to-[#cdbd9f] flex items-end justify-center gap-[3px] px-3 pt-5 border-b-[10px] border-[#6b4a2b]" aria-hidden="true">
                        @foreach ($spines as $spine)
                            <div class="relative shrink-0 rounded-[3px] shadow-[inset_-3px_0_4px_rgba(0,0,0,0.25)] overflow-hidden flex items-start justify-center pt-[38%]"
                                style="background: {{ $spine['bg'] }}; height: {{ $spine['h'] }}%; width: {{ $spine['w'] }}px">
                                <span class="absolute inset-x-0 top-[8%] h-[3px] bg-black/15"></span>
                                <span class="absolute inset-x-0 bottom-[8%] h-[3px] bg-black/15"></span>
                                @isset($spine['logo'])
                                    <img src="{{ Vite::asset('resources/images/brands/' . $spine['logo']) }}" alt=""
                                        class="-rotate-90 max-w-none h-[18px] w-auto">
                                @endisset
                            </div>
                        @endforeach
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/80 via-black/35 to-transparent pt-16 p-6">
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
