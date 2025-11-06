@php
    use Illuminate\Support\Facades\Vite;
    $categories = [
        [
            'name' => 'Hengeres fejű belső kulcsnyílású csavarok',
            'image' => Vite::asset('resources/images/categories/cat-01.webp'),
            'url' => '#',
        ],
        [
            'name' => 'Félgömbfejű csavarok',
            'image' => Vite::asset('resources/images/categories/cat-02.webp'),
            'url' => '#',
        ],
        [
            'name' => 'Süllyesztett fejű belső kulcsnyílású csavarok',
            'image' => Vite::asset('resources/images/categories/cat-03.webp'),
            'url' => '#',
        ],
        [
            'name' => 'Beállítócsavarok',
            'image' => Vite::asset('resources/images/categories/cat-04.webp'),
            'url' => '#',
        ],
        [
            'name' => 'Csődugók',
            'image' => Vite::asset('resources/images/categories/cat-05.webp'),
            'url' => '#',
        ],
        [
            'name' => 'Önzáró belső kulcsnyílású csavarok',
            'image' => Vite::asset('resources/images/categories/cat-06.webp'),
            'url' => '#',
        ],
        [
            'name' => 'Belső kulcsnyílású fejek',
            'image' => Vite::asset('resources/images/categories/cat-07.webp'),
            'url' => '#',
        ],
    ];
@endphp

<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Belső kulcsnyílású hernyócsavarok</h1>
        <div class="text-sm text-blue-600">
            <a href="#" class="hover:underline">További keresés</a>
        </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($categories as $category)
            <a href="{{ $category['url'] }}" class="block group">
                <div
                    class="h-full bg-white border border-gray-400 rounded-lg shadow-sm overflow-hidden transition-shadow group-hover:shadow-xl">
                    <div class="aspect-square">
                        <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}"
                            class="w-full h-full object-contain p-4">
                    </div>
                    <div class="p-4 text-center border-t border-gray-300">
                        <h3 class="text-sm font-medium text-gray-900 group-hover:text-blue-600">
                            {{ $category['name'] }}
                        </h3>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
