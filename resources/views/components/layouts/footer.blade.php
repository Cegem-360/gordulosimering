@use('App\Models\Category')

{{-- The product links point at real categories, found by name (and parent
     name, since names repeat), so a renamed or emptied category drops out
     instead of leaving a dead link. --}}
@php
    $productLinks = [
        ['label' => 'SKF csapágyak', 'parent' => Category::BRAND_ROOT_NAME, 'name' => 'SKF'],
        ['label' => 'LOCTITE termékek', 'parent' => Category::BRAND_ROOT_NAME, 'name' => 'LOCTITE'],
        ['label' => 'Szíjak és láncok', 'parent' => null, 'name' => 'HAJTÁSTECHNIKA'],
        ['label' => 'Szerszámok', 'parent' => null, 'name' => 'KÉZISZERSZÁMOK ÉS MŰSZEREK'],
        ['label' => 'Kenőanyagok', 'parent' => 'VEGYI ÁRUK', 'name' => 'ZSÍR, OLAJ'],
        ['label' => 'Tömítések', 'parent' => null, 'name' => 'TÖMÍTÉSEK'],
    ];
    $linkedCategories = Category::query()
        ->with('parentCategory:id,name')
        ->whereIn('name', array_column($productLinks, 'name'))
        ->get(['id', 'name', 'slug', 'category_id']);
    $productLinks = collect($productLinks)
        ->map(fn (array $link): array => [
            ...$link,
            'category' => $linkedCategories->first(fn (Category $category): bool => $category->name === $link['name']
                && $category->parentCategory?->name === $link['parent']),
        ])
        ->filter(fn (array $link): bool => $link['category'] !== null);
@endphp

<!-- Footer -->
<footer class="bg-[#00204A] text-white pt-16 pb-8">
    <div class="container mx-auto px-4">
        <!-- Newsletter Signup -->
        <div class="mb-16">
            <h2 class="text-2xl font-bold mb-4">Iratkozzon fel hírlevelünkre</h2>
            <p class="text-gray-300 mb-4">Legyen naprakész a termékekkel és szolgáltatásokkal kapcsolatos
                újdonságokról, akciókról és műszaki információkról.</p>
            <livewire:newsletter-signup />
            <p class="text-sm text-gray-400 mt-2">
                Bármikor leiratkozhat a gs@gordulo-simmering.hu címen. <a href="{{ route('privacy-policy') }}"
                    class="text-blue-400 hover:underline">Adatvédelmi nyilatkozatunkban</a> megtudhatja, hogyan kezeljük
                adatait.
            </p>
        </div>

        <!-- Footer Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            <!-- Miben segíthetünk? -->
            <div>
                <h3 class="text-lg font-semibold mb-6">Miben segíthetünk?</h3>
                <div class="flex items-center mb-4">
                    <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-headset text-2xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">24 órás ügyelet</p>
                        <a href="tel:+36309440203" class="text-xl font-bold hover:text-blue-400 transition-colors">+36
                            30 944 0203</a>
                    </div>
                </div>
            </div>

            <!-- Szolgáltatások -->
            <div>
                <h3 class="text-lg font-semibold mb-6">Szolgáltatások</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('products.index') }}" class="hover:text-blue-400 transition-colors">Webáruház</a></li>
                    <li><a href="{{ route('services') }}#ugyelet" class="hover:text-blue-400 transition-colors">24 órás csapágy ügyelet</a></li>
                    <li><a href="{{ route('services') }}#hazhozszallitas" class="hover:text-blue-400 transition-colors">Ingyenes házhozszállítás</a></li>
                    <li><a href="{{ route('services') }}#tovabbi-szolgaltatasok" class="hover:text-blue-400 transition-colors">SKF szervizszolgáltatás</a></li>
                    <li><a href="{{ route('services') }}#hazhozszallitas" class="hover:text-blue-400 transition-colors">Motoros futárszolgálat</a></li>
                    <li><a href="{{ route('services') }}#tovabbi-szolgaltatasok" class="hover:text-blue-400 transition-colors">Műszaki tanácsadás</a></li>
                </ul>
            </div>

            <!-- Termékek -->
            <div>
                <h3 class="text-lg font-semibold mb-6">Termékek</h3>
                <ul class="space-y-3">
                    @foreach ($productLinks as $link)
                        <li wire:key="footer-category-{{ $link['category']->id }}"><a href="{{ route('categories.show', $link['category']) }}" class="hover:text-blue-400 transition-colors">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Üzleteink -->
            <div>
                <h3 class="text-lg font-semibold mb-6">Üzleteink</h3>
                <ul class="space-y-4 text-sm">
                    <li>
                        <p class="font-medium text-blue-400">X. kerület</p>
                        <p>1102 Budapest, Kőrösi Csoma S. út 18-20.</p>
                        <a href="tel:+3612611566" class="hover:text-blue-400">Tel: +36 1 261 1566</a>
                    </li>
                    <li>
                        <p class="font-medium text-blue-400">XVII. kerület</p>
                        <p>1173 Budapest, Pesti út 203.</p>
                        <a href="tel:+3612574450" class="hover:text-blue-400">Tel: +36 1 257 4450</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="border-t border-white/10 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Copyright and Links -->
                <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm text-gray-400">
                    <span>© {{ date('Y') }} GÖRDÜLŐ-Simmering Kft.</span>
                    <a href="{{ route('privacy-policy') }}" class="hover:text-white transition-colors">Adatvédelmi
                        nyilatkozat</a>
                    <a href="{{ route('terms-and-conditions') }}" class="hover:text-white transition-colors">Általános szerződési feltételek</a>
                    <button type="button" x-data @click="$dispatch('open-cookie-settings')" class="hover:text-white transition-colors">Süti beállítások</button>
                </div>
                <!-- Payment Methods -->
                {{-- <div class="flex items-center gap-2">
                    <img src="{{ Vite::asset('resources/images/visa.png') }}" alt="Visa" class="h-8">
                    <img src="{{ Vite::asset('resources/images/mastercard.png') }}" alt="Mastercard" class="h-8">
                </div> --}}
            </div>
        </div>
    </div>
</footer>
