<div class="bg-white rounded-lg shadow-lg p-4 w-full">
    <h2 class="text-xl font-bold text-[#1e3a5f] mb-4 px-3">Termékeink</h2>

    <div class="space-y-0">
        @php
            $categories = [
                'Mélyhornyú golyóscsapágyak',
                'Beálló golyóscsapágyak',
                'Ferde hatásvonalú golyóscsapágyak',
                'Hengergörgős csapágyak',
                'Kúpgörgős csapágyak',
                'Tűgörgős csapágyak',
                'Hordógörgős csapágyak',
                'Axiális golyóscsapágyak',
                'Axiális beálló görgőscsapágyak',
                'Y csapágyak',
                'Csapágy házak és egységek',
                'Vezető- és támasztógörgők',
                'Gömbcsuklók',
                'Miniatür golyóscsapágyak',
            ];
        @endphp

        @foreach ($categories as $category)
            <a href="#" class="flex items-center gap-3 p-3 hover:bg-gray-200 rounded-lg group transition-colors">
                <span class="w-5 h-5 shrink-0">
                    <svg class="w-full h-full text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
                <span class="text-gray-700 grow text-xs">{{ $category }}</span>
                <svg class="w-4 h-4 text-gray-400 shrink-0 transform transition-transform group-hover:translate-x-1"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        @endforeach
    </div>
</div>
