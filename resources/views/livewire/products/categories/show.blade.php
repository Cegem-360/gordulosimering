<div>
    <div class="min-h-screen bg-gray-50">
        <!-- Breadcrumbs -->
        <div class="bg-white border-b">
            <div class="container mx-auto px-4 py-3">
                <div class="flex items-center flex-wrap gap-2 text-sm">
                    <a href="{{ route('categories.index') }}" class="text-blue-600 hover:underline">Termékkategóriák</a>
                    @foreach ($breadcrumbs as $crumb)
                        <span class="text-gray-500">&gt;</span>
                        @if ($loop->last)
                            <span class="text-gray-700">{{ $crumb->name }}</span>
                        @else
                            <a href="{{ route('categories.show', $crumb) }}"
                                class="text-blue-600 hover:underline">{{ $crumb->name }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            @php($parent = $category->parentCategory)
            <a href="{{ $parent ? route('categories.show', $parent) : route('categories.index') }}"
                class="inline-flex items-center gap-2 mb-4 text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                <i class="fas fa-arrow-left"></i>
                {{ $parent ? 'Vissza: ' . $parent->name : 'Vissza az összes kategóriához' }}
            </a>
            <h1 class="text-2xl font-bold mb-2">{{ $category->name }}</h1>
            <p class="text-sm text-gray-600 mb-6">
                {{ Number::format($products->total(), locale: 'hu') }} termék található
            </p>

            <!-- Subcategories -->
            @if ($subcategories->isNotEmpty())
                <div class="mb-10">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Alkategóriák</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($subcategories as ['category' => $subcategory, 'image' => $image])
                            <x-category-tile :category="$subcategory" :image="$image" wire:key="sub-{{ $subcategory->id }}" />
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Products -->
            @if ($this->hasProducts)
                <div class="grid md:grid-cols-[300px_1fr] gap-8">
                    <!-- Left: Filter Sidebar -->
                    <div>
                        <x-categories.filter-sidebar :filters="$filters" :selected="$selectedFilters" />
                    </div>

                    <!-- Right: Products Grid -->
                    <div>
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                            <h2 class="text-lg font-semibold text-gray-800">Termékek</h2>
                            <x-categories.active-filters :filters="$filters" :selected="$selectedFilters" :dimension-chips="$this->dimensionChips" />
                        </div>

                        @if ($products->count() > 0)
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($products as $product)
                                    <livewire:product-card :product="$product" :wire:key="'product-'.$product->id" />
                                @endforeach
                            </div>

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
            @elseif ($subcategories->isEmpty())
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nincs termék ebben a kategóriában</h3>
                    <p class="text-gray-600">Kérjük, nézzen körül a többi kategóriában.</p>
                </div>
            @endif
        </div>
    </div>
</div>
