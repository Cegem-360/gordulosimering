<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-8">Márkáink</h2>
        <div class="flex flex-wrap justify-center items-center gap-8 md:gap-12">
            @php
                // Heights adjusted for optical balance (wide logos smaller, compact logos larger)
                // 'invert' => true for white logos that need to be inverted on white background
                $brands = [
                    ['name' => 'SKF', 'logo' => 'skf.svg', 'height' => 'h-8'],
                    ['name' => 'LOCTITE', 'logo' => 'loctite.png', 'height' => 'h-8'],
                    ['name' => 'ZKL/ZVL', 'logo' => 'zkl.svg', 'height' => 'h-10'],
                    ['name' => 'FABORY', 'logo' => 'fabory.svg', 'height' => 'h-7'],
                    ['name' => 'DURACELL', 'logo' => 'duracell.svg', 'height' => 'h-7', 'invert' => true],
                    ['name' => 'ENERGIZER', 'logo' => 'energizer.png', 'height' => 'h-10'],
                    ['name' => 'NICRO', 'logo' => 'nicro-logo.png', 'height' => 'h-8'],
                    ['name' => 'NORMA', 'logo' => 'norma.svg', 'height' => 'h-12'],
                    ['name' => 'OKS', 'logo' => 'oks.png', 'height' => 'h-12'],
                    ['name' => 'TENTE', 'logo' => 'tente.png', 'height' => 'h-7'],
                    ['name' => 'SEEGER', 'logo' => 'seeger.svg', 'height' => 'h-12'],
                    ['name' => 'BAHCO', 'logo' => 'bahco.svg', 'height' => 'h-24'],
                    ['name' => 'KS-TOOLS', 'logo' => 'ks-tools.svg', 'height' => 'h-10'],
                    ['name' => 'BETA', 'logo' => 'beta-logo.png', 'height' => 'h-8'],
                    ['name' => 'TIMKEN', 'logo' => 'timken.png', 'height' => 'h-8'],
                    ['name' => 'FAG', 'logo' => 'fag.svg', 'height' => 'h-7'],
                    ['name' => 'INA', 'logo' => 'ina.svg', 'height' => 'h-20'],
                ];
            @endphp

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
