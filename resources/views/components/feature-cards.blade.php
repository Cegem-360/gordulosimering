<div class="bg-white py-12 lg:py-16">
    <div class="">
        <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
            <!-- Webshop Card -->
            <a href="http://webshop.gordulo-simmering.hu" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img src="{{ Vite::asset('resources/images/webshop-banner.webp') }}" alt="Webáruház"
                        class="w-full h-64 object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            Webáruház - 20% kedvezmény
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
            <a href="/szolgaltatasaink" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img src="{{ Vite::asset('resources/images/24-7-service.webp') }}" alt="24 órás ügyelet"
                        class="w-full h-64 object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            24 órás csapágy ügyelet
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                    </div>
                </div>
            </a>

            <!-- SKF Products Card -->
            <a href="/termekeink" class="group block">
                <div
                    class="relative overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
                    <img src="{{ Vite::asset('resources/images/skf-products.webp') }}" alt="SKF termékek"
                        class="w-full h-64 object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-t from-black/70 to-transparent p-6">
                        <h3 class="text-white text-xl font-semibold flex items-center gap-2">
                            Eredeti SKF termékek
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </h3>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
