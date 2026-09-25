@php
    /* Figures from the client's current site, gordulo-simmering.hu/szolgaltatasaink (2026-09-25). */
    $onCallRates = [
        ['when' => 'Munkanapokon (péntek kivételével)', 'what' => 'Soron kívüli üzletnyitás a Kőrösi Csoma úti üzletünkben, 1–2 órán belül', 'price' => '20 000 Ft + ÁFA'],
        ['when' => 'Hétvégén, ünnep- és pihenőnapokon', 'what' => 'Soron kívüli üzletnyitás a Kőrösi Csoma úti üzletünkben, 1–3 órán belül', 'price' => '22 000 Ft + ÁFA'],
        ['when' => 'Munkanapokon (péntek kivételével)', 'what' => 'Üzletnyitás és házhozszállítás Budapest területén, 2–3 órán belül', 'price' => '25 000 Ft + ÁFA'],
        ['when' => 'Hétvégén, ünnep- és pihenőnapokon', 'what' => 'Üzletnyitás és házhozszállítás Budapest területén, 2–3 órán belül', 'price' => '28 000 Ft + ÁFA'],
    ];

    $freeDelivery = [
        ['threshold' => '80 000 Ft felett', 'area' => 'VIII., IX., X., XIII., XIV., XV., XVI., XVII., XVIII., XIX. és XX. kerület'],
        ['threshold' => '100 000 Ft felett', 'area' => 'IV., V., VI., VII., XXI. és XXIII. kerület'],
        ['threshold' => '120 000 Ft felett', 'area' => 'I., II., III., XI., XII. és XXII. kerület, valamint Pécel, Gyál, Maglód, Csömör, Kistarcsa, Nagytarcsa, Ecser, Taksony, Vecsés és Dunaharaszti'],
    ];

    $courierRates = [
        ['price' => '7 990 Ft + ÁFA', 'area' => 'VII., VIII., IX., X., XIII., XIV. és XIX. kerület'],
        ['price' => '8 990 Ft + ÁFA', 'area' => 'I., V., VI., XV., XVI., XVII., XVIII. és XX. kerület'],
        ['price' => '9 990 Ft + ÁFA', 'area' => 'II., III., IV., XI., XII., XXI., XXII. és XXIII. kerület'],
        ['price' => '10 990 Ft + ÁFA', 'area' => 'Pécel, Gyál, Maglód, Csömör, Kistarcsa, Nagytarcsa, Ecser, Taksony, Vecsés és Dunaharaszti'],
    ];

    $moreServices = [
        ['icon' => 'fa-tools', 'title' => 'SKF szervizszolgáltatás', 'text' => 'SKF Szerződött Partnerként szerelési és karbantartási feladatokban is segítünk.'],
        ['icon' => 'fa-network-wired', 'title' => 'Online kapcsolat az SKF-fel', 'text' => 'Közvetlen online kapcsolat az SKF raktáraival és gyáraival, így gyorsan tudjuk a készletet és a szállítási időt.'],
        ['icon' => 'fa-cogs', 'title' => 'Műszaki szaktanácsadás', 'text' => 'Szakértő kollégáink segítenek a megfelelő termék kiválasztásában és a műszaki kérdésekben.'],
        ['icon' => 'fa-book-open', 'title' => 'Ingyenes katalógusok', 'text' => 'Kérésre ingyenesen elküldjük termékkatalógusainkat.'],
        ['icon' => 'fa-chalkboard-teacher', 'title' => 'Oktatások szervezése', 'text' => 'Termék- és szerelési oktatásokat szervezünk partnereinknek.'],
        ['icon' => 'fa-box-open', 'title' => 'Mintadarabok bemutatása', 'text' => 'Üzleteinkben mintadarabokon mutatjuk be a termékeket.'],
        ['icon' => 'fa-credit-card', 'title' => 'Kártyaelfogadás', 'text' => 'Üzleteinkben bankkártyával is fizethet.'],
        ['icon' => 'fa-shopping-cart', 'title' => 'Webáruház', 'text' => 'Online rendelés 0–24, minimum 20% kedvezménnyel a listaárakból. Üzleteink készlete is itt látható.'],
    ];
@endphp

<div>
    <!-- Hero Section -->
    <div class="bg-linear-to-r from-blue-900 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Szolgáltatásaink</h1>
                <p class="text-xl text-blue-100">A GÖRDÜLŐ-Simmering Kft. vevőinek az általános alapszolgáltatáson
                    túl további szolgáltatásokat is nyújt. Az SKF Szerződött Partnereként teljes körű megoldásokat
                    kínálunk a csapágyak és kapcsolódó termékek területén.</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16 space-y-24">
        <!-- 24/7 On-call Service -->
        <section class="grid lg:grid-cols-2 gap-8">
            <div class="order-2 lg:order-1 bg-blue-50 rounded-2xl p-6 sm:p-8">
                <span class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    24/7 Ügyelet</span>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Csapágy éjjel-nappal</h2>
                <p class="text-gray-600 mb-6">Ha munkaidőn kívül, hétvégén vagy ünnepnapon azonnal csapágyra van
                    szüksége, ügyeleti díj ellenében soron kívül kinyitjuk az üzletet, vagy házhoz is szállítjuk
                    Budapesten. Üzleteink készlete a webáruházban megtekinthető.</p>

                <div class="flex items-center gap-4 mb-6">
                    <div class="shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-phone text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ügyeleti telefonszám</p>
                        <a href="tel:+36309440203" class="text-xl font-bold text-blue-600 hover:text-blue-700">+36 30 944
                            0203</a>
                    </div>
                </div>

                <dl class="divide-y divide-blue-100 bg-white rounded-xl border border-blue-100">
                    @foreach ($onCallRates as $rate)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4 p-4">
                            <dt class="grow">
                                <span class="block font-semibold text-gray-900">{{ $rate['when'] }}</span>
                                <span class="block text-sm text-gray-600">{{ $rate['what'] }}</span>
                            </dt>
                            <dd class="shrink-0 font-bold text-blue-700 whitespace-nowrap">{{ $rate['price'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <p class="mt-6 flex gap-3 text-sm text-gray-700 bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <i class="fas fa-info-circle text-amber-600 mt-0.5"></i>
                    <span>Az ügyeleti szám csak sürgős, munkaidőn kívüli üzletnyitásra szolgál. Munkaidőben és ha
                        csak információra van szüksége, kérjük, üzleteinket hívja.</span>
                </p>
            </div>
            <div class="order-1 lg:order-2">
                <img src="{{ Vite::asset('resources/images/24-7-service.webp') }}" alt="24/7 ügyelet"
                    class="rounded-2xl shadow-xl w-full lg:h-full object-cover">
            </div>
        </section>

        <!-- Delivery -->
        <section>
            <div class="max-w-3xl mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Házhozszállítás</h2>
                <p class="text-gray-600">Budapesten és környékén értékhatártól függően ingyen szállítunk házhoz. Az
                    értékhatár alatti csomagokat motoros futárral, 2 órán belül visszük ki.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 p-5 border-b bg-gray-50">
                        <i class="fas fa-truck text-blue-600 text-xl"></i>
                        <h3 class="text-xl font-bold text-gray-900">Ingyenes házhozszállítás</h3>
                    </div>
                    <dl class="divide-y">
                        @foreach ($freeDelivery as $row)
                            <div class="grid sm:grid-cols-[9rem_1fr] gap-1 sm:gap-4 p-5">
                                <dt class="font-bold text-blue-700">{{ $row['threshold'] }}</dt>
                                <dd class="text-gray-700">{{ $row['area'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="px-5 pb-5 text-sm text-gray-500">Az értékhatárok bruttó rendelési értékre vonatkoznak.</p>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 p-5 border-b bg-gray-50">
                        <i class="fas fa-motorcycle text-blue-600 text-xl"></i>
                        <h3 class="text-xl font-bold text-gray-900">Motoros futár értékhatár alatt</h3>
                    </div>
                    <dl class="divide-y">
                        @foreach ($courierRates as $row)
                            <div class="grid sm:grid-cols-[9rem_1fr] gap-1 sm:gap-4 p-5">
                                <dt class="font-bold text-blue-700 whitespace-nowrap">{{ $row['price'] }}</dt>
                                <dd class="text-gray-700">{{ $row['area'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="px-5 pb-5 text-sm text-gray-500">Legfeljebb 6 kg-os csomagig, 2 órán belüli
                        kiszállítással.</p>
                </div>
            </div>
        </section>

        <!-- SKF Quality -->
        <section class="bg-linear-to-r from-blue-900 to-blue-800 text-white rounded-2xl p-8 md:p-12">
            <div class="grid md:grid-cols-[auto_1fr] gap-6 items-center">
                <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center">
                    <i class="fas fa-certificate text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-3">Nálunk ha SKF minőségért fizet, akkor SKF minőséget
                        kap.</h2>
                    <p class="text-blue-100">Kérésre minden SKF csapágyunk mellé minőségi tanúsítványt adunk. Stop a
                        hamis csapágyaknak!</p>
                </div>
            </div>
        </section>

        <!-- More Services -->
        <section>
            <h2 class="text-3xl font-bold text-gray-900 mb-8">További szolgáltatásaink</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($moreServices as $service)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas {{ $service['icon'] }} text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-gray-900">{{ $service['title'] }}</h3>
                        <p class="text-gray-600 text-sm">{{ $service['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Contact Section -->
        <section class="bg-gray-50 rounded-2xl p-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">Személyes ügyintézés</h2>
                <p class="text-gray-600 mb-8">Keressen fel minket személyesen üzleteinkben, ahol szakértő kollégáink
                    várják Önt, vagy írjon nekünk a
                    <a href="mailto:gs@gordulo-simmering.hu"
                        class="font-medium text-blue-600 hover:text-blue-700">gs@gordulo-simmering.hu</a> címre.
                </p>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-2">X. kerület</h3>
                        <p class="text-gray-600 mb-2">1102 Budapest, Kőrösi Csoma S. út 18-20.</p>
                        <a href="tel:+3612611566" class="text-blue-600 hover:text-blue-700 font-medium">+36 1 261
                            1566</a>
                    </div>

                    <div class="bg-white rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-2">XVII. kerület</h3>
                        <p class="text-gray-600 mb-2">1173 Budapest, Pesti út 203.</p>
                        <a href="tel:+3612574450" class="text-blue-600 hover:text-blue-700 font-medium">+36 1 257
                            4450</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
