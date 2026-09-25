@props(['filters' => [], 'selected' => []])

{{-- Each section shows its first `visible` items; "Összes mutatása" reveals
     the rest. A ticked item always stays visible so it can be unticked. --}}
<div class="bg-white rounded-lg shadow p-4">
    @forelse($filters as $filter)
        @php
            $selectedValues = $selected[$filter['key']] ?? [];
            $hiddenCount = collect($filter['items'])
                ->slice($filter['visible'])
                ->reject(fn ($item) => in_array($item['value'], $selectedValues, true))
                ->count();
        @endphp
        <div x-data="{ open: true, expanded: false }" class="mb-6 last:mb-0" wire:key="filter-{{ $filter['key'] }}">
            <h3 class="text-lg font-semibold mb-3 flex items-center justify-between">
                {{ $filter['title'] }}
                <button type="button" @click="open = !open" class="text-sm text-gray-500">
                    <i class="fas fa-chevron-up transition-transform" :class="{ 'rotate-180': !open }"></i>
                </button>
            </h3>
            <div x-show="open" x-collapse class="space-y-2">
                @isset($filter['search'])
                    <input type="search"
                        wire:model.live.debounce.300ms="{{ $filter['search']['model'] }}"
                        placeholder="{{ $filter['search']['placeholder'] }}"
                        class="w-full mb-1 px-3 py-1.5 rounded-md border border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                @endisset

                @forelse($filter['items'] as $item)
                    @php($isCollapsible = $loop->index >= $filter['visible'] && ! in_array($item['value'], $selectedValues, true))
                    <label class="flex items-center gap-2 cursor-pointer group"
                        wire:key="filter-{{ $filter['key'] }}-{{ $item['value'] }}"
                        @if ($isCollapsible) x-show="expanded" x-cloak @endif>
                        <input
                            type="checkbox"
                            wire:model.live="selectedFilters.{{ $filter['key'] }}"
                            value="{{ $item['value'] }}"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-blue-600 truncate" title="{{ $item['name'] }}">
                            {{ \Illuminate\Support\Str::limit($item['name'], 35) }}
                        </span>
                        <span class="text-xs text-gray-500 ml-auto shrink-0">({{ Number::format($item['count'], locale: 'hu') }})</span>
                    </label>
                @empty
                    @if (filled($filter['search']['empty'] ?? null))
                        <p class="text-sm text-gray-500">{{ $filter['search']['empty'] }}</p>
                    @endif
                @endforelse

                @if ($hiddenCount > 0)
                    <button type="button" class="pt-1 text-sm text-blue-600 hover:underline" @click="expanded = !expanded">
                        <span x-text="expanded ? 'Kevesebb' : 'Összes mutatása ({{ $hiddenCount }})'">Összes mutatása ({{ $hiddenCount }})</span>
                    </button>
                @endif
            </div>
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
