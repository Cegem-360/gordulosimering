@props(['filters' => [], 'selected' => [], 'rangeChips' => []])

{{-- The removable chips of the ticked filters and the dimension ranges. The
     slot holds page-specific chips shown first, like the search term. --}}
@php
    $hasActiveFilters = collect($selected)->flatten()->isNotEmpty() || $rangeChips !== [] || $slot->hasActualContent();
    $filterLabels = collect($filters)->mapWithKeys(fn ($filter) => [$filter['key'] => collect($filter['items'])->pluck('name', 'value')]);
@endphp

@if ($hasActiveFilters)
    <div class="flex flex-wrap gap-2">
        {{ $slot }}
        @foreach ($selected as $key => $values)
            @foreach ($values as $value)
                <span wire:key="chip-{{ $key }}-{{ $value }}"
                    class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                    {{ $filterLabels[$key][$value] ?? $value }}
                    <button type="button"
                        wire:click="$set('selectedFilters.{{ $key }}', {{ json_encode(array_values(array_diff($values, [$value]))) }})"
                        class="hover:text-blue-600">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </span>
            @endforeach
        @endforeach
        @foreach ($rangeChips as $chip)
            <span wire:key="chip-dimension-{{ $chip['key'] }}"
                class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                {{ $chip['label'] }}
                <button type="button" wire:click="clearDimensionRange('{{ $chip['key'] }}')"
                    class="hover:text-blue-600">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        @endforeach
    </div>
@endif
