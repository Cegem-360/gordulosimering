<div class="relative w-full" x-data="{ open: @entangle('showResults') }" @click.outside="open = false">
    <form wire:submit="search" class="relative">
        <input type="text" wire:model.live.debounce.300ms="query" @focus="$wire.showResultsPanel()"
            placeholder="{{ __('Keresés termékek között... (pl: 6205-2RS, SKF golyóscsapágy)') }}"
            class="w-full pl-12 pr-4 py-2 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            autocomplete="off">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
        </div>
        <div wire:loading wire:target="query" class="absolute inset-y-0 right-0 pr-4 flex items-center">
            <i class="fas fa-spinner fa-spin text-gray-400"></i>
        </div>
    </form>

    <!-- Search Results Dropdown -->
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
        @if ($this->results->count() > 0)
            <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @foreach ($this->results as $product)
                    <li>
                        <button type="button" wire:click="goToProduct('{{ $product->slug }}')"
                            class="w-full flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors text-left">
                            <!-- Product Image -->
                            <div class="w-12 h-12 bg-gray-100 rounded shrink-0 overflow-hidden">
                                @if ($product->images)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-box text-gray-300"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">
                                    <span class="font-mono">{{ $product->product_code }}</span>
                                    @if ($product->minimum_stock > 0)
                                        <span class="ml-2 text-green-600"><i class="fas fa-check-circle"></i>
                                            Készleten</span>
                                    @endif
                                </p>
                            </div>

                            <!-- Price -->
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-blue-600">
                                    {{ number_format($product->net_selling_price, 0, ',', ' ') }} Ft
                                </p>
                                <p class="text-xs text-gray-400">nettó</p>
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- View All Results -->
            <div class="border-t border-gray-100 p-3 bg-gray-50">
                <button type="button" wire:click="search"
                    class="w-full text-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                    {{ __('Összes találat megtekintése') }} <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        @elseif (mb_strlen($query) >= 2)
            <div class="p-6 text-center text-gray-500">
                <i class="fas fa-search text-3xl text-gray-300 mb-2"></i>
                <p>{{ __('Nincs találat') }} "{{ $query }}"</p>
                <p class="text-sm mt-1">{{ __('Próbáljon más kulcsszót') }}</p>
            </div>
        @endif
    </div>
</div>
