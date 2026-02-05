<x-layouts.app>
    <div class="container mx-auto grid md:grid-cols-[1fr_3fr] gap-3 px-4 py-4 lg:py-0">
        <!-- Left: Category Selector -->
        <x-category-selector />
        <!-- Right: Hero Banner -->
        <div>
            <x-hero ctaUrl="#" />
            <!-- Feature Cards Section -->
            <x-feature-cards faboryAppUrl="#" faboryLogicUrl="#" innovationUrl="#" />
        </div>
    </div>
    <!-- Brand Logos Section -->
    <x-brand-logos />

    <!-- Featured Products Section -->
    <x-featured-products />

    <!-- Innovation Section -->
    <x-innovation />

</x-layouts.app>
