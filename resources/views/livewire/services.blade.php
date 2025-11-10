<div>
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-blue-900 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Szolgáltatásaink</h1>
                <p class="text-xl text-blue-100">Az SKF hivatalos partnereként teljes körű megoldásokat kínálunk
                    ügyfeleinknek a csapágyak és kapcsolódó termékek területén.</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-16">
        <!-- 24/7 Emergency Service -->
        <div class="grid md:grid-cols-2 gap-8 items-center mb-24">
            <div class="order-2 md:order-1">
                <div class="bg-blue-50 rounded-2xl p-8">
                    <span
                        class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold mb-4">24/7
                        Ügyelet</span>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Csapágy éjjel-nappal</h2>
                    <p class="text-gray-600 mb-6">Munkaidőn kívül, ünnepnapokon is igénybe vehető sürgősségi
                        szolgáltatás a termeléskiesés minimalizálásáért.</p>
                    <div class="flex items-center gap-4">
                        <div class="shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-phone text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sürgős esetben hívja ügyeletünket</p>
                            <a href="tel:+36309440203" class="text-xl font-bold text-blue-600 hover:text-blue-700">+36
                                30 944 0203</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="order-1 md:order-2">
                <img src="{{ Vite::asset('resources/images/24-7-service.webp') }}" alt="24/7 Ügyelet"
                    class="rounded-2xl shadow-xl w-full">
            </div>
        </div>

        <!-- Service Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Free Delivery -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-sm">
                <div class="p-5">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-truck text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Ingyenes házhozszállítás</h3>
                    <p class="text-gray-700">Budapesten és 50km-es körzetében, értékhatártól függően ingyenes
                        kiszállítás.
                    </p>
                </div>
            </div>

            <!-- Motorcycle Courier -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-sm">
                <div class="p-5">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-motorcycle text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Motoros futárszolgálat</h3>
                    <p class="text-gray-700">Sürgős esetekben motoros futárral biztosítjuk a leggyorsabb kiszállítást.
                    </p>
                </div>
            </div>

            <!-- Technical Support -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-sm">
                <div class="p-5">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-cogs text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Műszaki szaktanácsadás</h3>
                    <p class="text-gray-700">Szakértő csapatunk segít a megfelelő termék kiválasztásában és a technikai
                        kérdésekben.</p>
                </div>
            </div>

            <!-- SKF Service -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-sm">
                <div class="p-5">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-tools text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">SKF szervízszolgáltatások</h3>
                    <p class="text-gray-700">Közvetlen kapcsolat az SKF raktáraival és gyáraival, eredeti alkatrészek és
                        szerviz.</p>
                </div>
            </div>

            <!-- Training -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-sm">
                <div class="p-5">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Oktatás és terméktámogatás</h3>
                    <p class="text-gray-700">Rendszeres oktatások szervezése, mintadarabok bemutatása és
                        alkalmazástechnikai
                        támogatás.</p>
                </div>
            </div>

            <!-- Online Shop -->
            <div class="bg-white border border-gray-300 rounded-lg shadow-sm">
                <div class="p-5">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Webáruház</h3>
                    <p class="text-gray-700">Online rendelés 0-24, minimum 20% kedvezménnyel a listaárakból.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="mt-24 bg-gray-50 rounded-2xl p-8">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-3xl font-bold mb-6">Személyes ügyintézés</h2>
            <p class="text-gray-600 mb-8">Keressen fel minket személyesen üzleteinkben, ahol szakértő kollégáink
                várják Önt!</p>

            <div class="grid md:grid-cols-3 gap-6">
                <!-- X. kerület -->
                <div class="bg-white rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">X. kerület</h3>
                    <p class="text-gray-600 mb-2">Kőrösi Csoma S. út 18-20.</p>
                    <a href="tel:+3612611566" class="text-blue-600 hover:text-blue-700 font-medium">+36 1 261
                        1566</a>
                </div>

                <!-- XIV. kerület -->
                <div class="bg-white rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">XIV. kerület</h3>
                    <p class="text-gray-600 mb-2">Nagy Lajos kir. útja 117.</p>
                    <a href="tel:+3613830951" class="text-blue-600 hover:text-blue-700 font-medium">+36 1 383
                        0951</a>
                </div>

                <!-- XVII. kerület -->
                <div class="bg-white rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">XVII. kerület</h3>
                    <p class="text-gray-600 mb-2">Pesti út 203.</p>
                    <a href="tel:+3612574450" class="text-blue-600 hover:text-blue-700 font-medium">+36 1 257
                        4450</a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
