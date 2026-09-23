<x-layouts.app>
    <div class="container mx-auto grid md:grid-cols-[1fr_3fr] gap-3 px-4 py-4 lg:py-0">
        <!-- Left: Search and Category Selector -->
        <div class="flex flex-col gap-3">
            <div class="bg-brand-blue rounded-lg shadow-lg p-4">
                <h2 class="text-lg font-bold text-white mb-3">{{ __('Keresés a termékek között') }}</h2>
                <livewire:live-search />
            </div>
            <x-category-selector />
        </div>
        <!-- Right: Hero Banner -->
        <div>
            <x-hero ctaUrl="#" />
            <!-- Feature Cards Section -->
            <x-feature-cards faboryAppUrl="#" faboryLogicUrl="#" innovationUrl="#" />
        </div>
    </div>
    <!-- Featured Products Section -->
    <x-featured-products />

    <!-- Innovation Section -->
    <x-innovation />

</x-layouts.app>
