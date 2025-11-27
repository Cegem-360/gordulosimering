@props(['filters' => []])

<div class="bg-white rounded-lg shadow p-4">
    @forelse($filters as $filter)
        <div x-data="{ open: true }" class="mb-6 last:mb-0">
            <h3 class="text-lg font-semibold mb-3 flex items-center justify-between">
                {{ $filter['title'] }}
                <button @click="open = !open" class="text-sm text-gray-500">
                    <i class="fas fa-chevron-up transition-transform" :class="{ 'rotate-180': !open }"></i>
                </button>
            </h3>
            <div x-show="open" x-collapse class="space-y-2">
                @foreach($filter['items'] as $item)
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="checkbox"
                            wire:model.live="selectedFilters.{{ $filter['key'] }}"
                            value="{{ $item['value'] }}"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-blue-600 truncate" title="{{ $item['name'] }}">
                            {{ \Illuminate\Support\Str::limit($item['name'], 35) }}
                        </span>
                        <span class="text-xs text-gray-500 ml-auto shrink-0">({{ number_format($item['count']) }})</span>
                    </label>
                @endforeach
            </div>
            @if(count($filter['items']) > 5 && !$loop->last)
                <div class="mt-3">
                    <button
                        type="button"
                        class="text-sm text-blue-600 hover:underline"
                        x-data="{ expanded: false }"
                        @click="expanded = !expanded"
                    >
                        <span x-text="expanded ? 'Kevesebb' : 'Összes mutatása'"></span>
                    </button>
                </div>
            @endif
        </div>
    @empty
        <p class="text-gray-500 text-sm">Nincs elérhető szűrő.</p>
    @endforelse

    @if(count($filters) > 0)
        <div class="mt-6 pt-4 border-t">
            <button
                type="button"
                wire:click="clearFilters"
                class="w-full py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors"
            >
                <i class="fas fa-times mr-2"></i>
                Szűrők törlése
            </button>
        </div>
    @endif
</div>
