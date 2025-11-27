@php
    $filters = [
        [
            'title' => 'Kategória',
            'items' => [
                ['name' => 'Hengeres fejű belső kulcsnyílású csavarok', 'count' => 6610],
                ['name' => 'Beállítócsavarok', 'count' => 5072],
                ['name' => 'Süllyesztett fejű belső kulcsnyílású csavarok', 'count' => 1385],
            ],
        ],
        [
            'title' => 'Szabvány',
            'items' => [
                ['name' => 'DIN 912', 'count' => 4624],
                ['name' => 'NF E25-125', 'count' => 4624],
                ['name' => 'ASME B18.3.1M', 'count' => 4624],
            ],
        ],
        [
            'title' => 'Átmérő',
            'items' => [
                ['name' => 'M8', 'count' => 1538],
                ['name' => 'M6', 'count' => 1493],
                ['name' => 'M10', 'count' => 1347],
            ],
        ],
        [
            'title' => 'Hosszúság',
            'items' => [
                ['name' => '30 mm', 'count' => 621],
                ['name' => '20 mm', 'count' => 615],
            ],
        ],
    ];
@endphp

<div class="bg-white rounded-lg shadow p-4">
    @foreach($filters as $filter)
        <div class="mb-6 last:mb-0">
            <h3 class="text-lg font-semibold mb-3 flex items-center justify-between">
                {{ $filter['title'] }}
                <button class="text-sm text-gray-500">
                    <i class="fas fa-chevron-up"></i>
                </button>
            </h3>
            <div class="space-y-2">
                @foreach($filter['items'] as $item)
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 group-hover:text-blue-600">{{ $item['name'] }}</span>
                        <span class="text-xs text-gray-500 ml-auto">({{ $item['count'] }})</span>
                    </label>
                @endforeach
            </div>
            @if(!$loop->last)
                <div class="mt-4">
                    <a href="#" class="text-sm text-blue-600 hover:underline">Összes mutatása</a>
                </div>
            @endif
        </div>
    @endforeach
</div>