<div
    class="relative bg-linear-to-br from-[#1e3a5f] to-[#2c5282] overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300">
    <!-- Arrow-like background decoration -->
    <svg class="absolute z-1 top-0 bottom-0 left-[40%] w-[800px] hidden md:block h-full text-[hsl(214,52%,20%)]"
        viewBox="0 0 800 1000" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,500L200,0L800,0L800,1000L200,1000L0,500Z" fill="currentColor" />
    </svg>

    <div class="px-6 lg:px-0 py-6 lg:py-0">
        <div class="grid lg:grid-cols-2 gap-4 md:gap-0 items-center">
            <!-- Image Section -->
            <div class="relative z-0 order-2 lg:order-1">
                <img src="{{ Vite::asset('resources/images/hero-img.webp') }}" alt=""
                    class="rounded-lg md:rounded-none shadow-2xl w-full h-auto">
            </div>

            <!-- Content Section -->
            <div class="relative z-10 order-1 lg:order-2 text-white">
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight mb-6">
                    {{ __('SKF csapágyak és kapcsolódó termékek hivatalos forgalmazója') }}
                </h1>

                <p class="text-lg lg:text-xl mb-8 text-gray-200">
                    {{ __('1993 óta az ipar és a lakosság szolgálatában.') }}<br>
                    {{ __('Több, mint 50.000 termék azonnal, raktárról.') }}
                </p>

                <div class="space-y-4">
                    <a href="{{ route('products.index') }}"
                        class="inline-block bg-[#22c55e] hover:bg-[#16a34a] text-white font-semibold px-8 py-4 rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
                        {{ __('Termékek böngészése') }}
                    </a>
                    <div class="text-sm text-gray-300">
                        <p>24 órás ügyelet: <a href="tel:+36309440203" class="font-bold hover:text-white">+36 30 944 0203</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
