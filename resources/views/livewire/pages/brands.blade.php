<div>
    <div class="bg-linear-to-r from-blue-900 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Márkáink</h1>
                <p class="text-xl text-blue-100">SKF szerződött partnerként a vezető csapágy-, tömítés- és
                    szerszámgyártók termékeit forgalmazzuk.</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach ($brands as $brand)
                @php($linked = $brand['filter'] !== null && $brand['product_count'] > 0)
                <{{ $linked ? 'a' : 'div' }}
                    @if ($linked) href="{{ route('categories.index', ['marka' => $brand['filter']]) }}" @endif
                    @class([
                        'group bg-white border border-gray-300 rounded-lg shadow-sm p-5 flex flex-col items-center gap-4',
                        'hover:shadow-lg hover:border-blue-400 transition' => $linked,
                    ])>
                    <div class="h-24 flex items-center justify-center">
                        <img src="{{ Vite::asset('resources/images/brands/' . $brand['logo']) }}" alt="{{ $brand['name'] }}"
                            @class([$brand['height'], 'w-auto max-w-full', 'invert' => $brand['invert'] ?? false])>
                    </div>
                    @if ($linked)
                        <span class="text-sm text-blue-600 group-hover:underline">
                            {{ Number::format($brand['product_count'], locale: 'hu') }} termék &rarr;
                        </span>
                    @else
                        <span class="text-sm text-gray-500">Érdeklődjön üzleteinkben</span>
                    @endif
                </{{ $linked ? 'a' : 'div' }}>
            @endforeach
        </div>
    </div>
</div>
