<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-8">Márkáink</h2>
        <div class="flex flex-wrap justify-center items-center gap-8 md:gap-12">
            @php($brands = config('brands'))

            @foreach ($brands as $brand)
                <div class="flex items-center justify-center px-4 py-2">
                    @if ($brand['logo'])
                        <img src="{{ Vite::asset('resources/images/brands/' . $brand['logo']) }}"
                            alt="{{ $brand['name'] }}"
                            class="{{ $brand['height'] }} w-auto {{ $brand['invert'] ?? false ? 'invert' : '' }} grayscale hover:grayscale-0 hover:invert-0 transition-all duration-300">
                    @else
                        <span
                            class="text-lg font-semibold text-gray-600 hover:text-blue-600 transition-colors duration-300">
                            {{ $brand['name'] }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
