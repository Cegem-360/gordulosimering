<div>
    <div class="min-h-screen bg-gray-50">
        <!-- Breadcrumbs -->
        <div class="bg-white border-b">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center flex-wrap gap-2 text-sm">
                    <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">Termékek</a>
                    <span class="text-gray-500">&gt;</span>
                    <span class="text-gray-700">Termékkategóriák</span>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="grid md:grid-cols-[300px_1fr] gap-8">
                <!-- Left: Filter Sidebar -->
                <div>
                    <x-categories.filter-sidebar :filters="$filters" />
                </div>

                <!-- Right: Products Grid -->
                <div>
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-2xl font-bold">Termékek</h1>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ number_format($products->total()) }} termék található
                            </p>
                        </div>

                        <!-- Active Filters -->
                        @php
                            $hasActiveFilters = collect($selectedFilters)->flatten()->isNotEmpty();
                        @endphp
                        @if ($hasActiveFilters)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($selectedFilters as $key => $values)
                                    @foreach ($values as $value)
                                        <span
                                            class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                            {{ $value }}
                                            <button type="button"
                                                wire:click="$set('selectedFilters.{{ $key }}', {{ json_encode(array_values(array_diff($values, [$value]))) }})"
                                                class="hover:text-blue-600">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </span>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Products Grid -->
                    @if ($products->count() > 0)
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach ($products as $product)
                                <livewire:product-card :product="$product" :wire:key="'product-'.$product->id" />
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-8">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="bg-white rounded-lg shadow p-8 text-center">
                            <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nincs találat</h3>
                            <p class="text-gray-600 mb-4">A megadott szűrőkkel nem található termék.</p>
                            <button type="button" wire:click="clearFilters"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-times mr-2"></i>
                                Szűrők törlése
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
