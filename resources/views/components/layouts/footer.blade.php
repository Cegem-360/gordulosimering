<!-- Footer -->
<footer class="bg-[#00204A] text-white pt-16 pb-8">
    <div class="container mx-auto px-4">
        <!-- Newsletter Signup -->
        <div class="mb-16">
            <h2 class="text-2xl font-bold mb-4">Iratkozzon fel hírlevelünkre</h2>
            <p class="text-gray-300 mb-4">Legyen naprakész a termékekkel és szolgáltatásokkal kapcsolatos
                újdonságokról, akciókról és műszaki információkról.</p>
            <form class="flex flex-col sm:flex-row gap-4 max-w-2xl">
                <input type="email" placeholder="Adja meg az email címét"
                    class="flex-1 px-4 py-3 rounded bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:outline-none focus:border-white/40">
                <button type="submit"
                    class="px-8 py-3 bg-green-600 hover:bg-green-700 rounded font-medium transition-colors">
                    Regisztrálok
                </button>
            </form>
            <p class="text-sm text-gray-400 mt-2">
                Bármikor leiratkozhat. <a href="/adatkezelesi-tajekoztato"
                    class="text-blue-400 hover:underline">Adatvédelmi szabályzatunkban</a> megtudhatja, hogyan kezeljük
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
                    <li><a href="/webshop" class="hover:text-blue-400 transition-colors">Webáruház</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">24 órás csapágy ügyelet</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">Ingyenes házhozszállítás</a>
                    </li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">SKF szervízszolgáltatások</a>
                    </li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">Motoros futárszolgálat</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">Műszaki tanácsadás</a></li>
                </ul>
            </div>

            <!-- Termékek -->
            <div>
                <h3 class="text-lg font-semibold mb-6">Termékek</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="hover:text-blue-400 transition-colors">SKF Csapágyak</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">LOCTITE termékek</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">Szíjak és láncok</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">Szerszámok</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">Kenőanyagok</a></li>
                    <li><a href="#" class="hover:text-blue-400 transition-colors">Tömítések</a></li>
                </ul>
            </div>

            <!-- Üzleteink -->
            <div>
                <h3 class="text-lg font-semibold mb-6">Üzleteink</h3>
                <ul class="space-y-4 text-sm">
                    <li>
                        <p class="font-medium text-blue-400">X. kerület</p>
                        <p>Kőrösi Csoma S. út 18-20.</p>
                        <a href="tel:+3612611566" class="hover:text-blue-400">Tel: +36 1 261 1566</a>
                    </li>
                    <li>
                        <p class="font-medium text-blue-400">XIV. kerület</p>
                        <p>Nagy Lajos kir. útja 117.</p>
                        <a href="tel:+3613830951" class="hover:text-blue-400">Tel: +36 1 383 0951</a>
                    </li>
                    <li>
                        <p class="font-medium text-blue-400">XVII. kerület</p>
                        <p>Pesti út 203.</p>
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
                    <a href="/adatkezelesi-tajekoztato" class="hover:text-white transition-colors">Adatvédelmi
                        politika</a>
                    <a href="/gdpr" class="hover:text-white transition-colors">GDPR</a>
                    <a href="#" class="hover:text-white transition-colors">Általános szerződési feltételek</a>
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
