<!-- Main Navigation -->
<div x-data="{ mobileMenuOpen: false, categoryMenuOpen: false }" class="bg-white border-b">
    <div class="container mx-auto px-4">
        <nav class="flex items-center justify-between h-16">
            <!-- Logo and Navigation -->
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <!-- Logo -->
                <a href="/" class="shrink-0">
                    <img src="{{ Vite::asset('resources/images/GS-logo.webp') }}"
                        alt="Gördülő Simering Kft - SKF csapágyak és kapcsolódó termékek kereskedése" class="h-12">
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex lg:items-center lg:space-x-6">
                    <!-- Category Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @mouseenter="open = true" @mouseleave="open = false"
                            class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 font-medium">
                            <span>{{ __('Termékkategóriák') }}</span>
                            <i class="fas fa-chevron-down text-sm transition-transform"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <!-- Category Dropdown -->
                        <div x-show="open" @mouseenter="open = true" @mouseleave="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 z-10 mt-2 w-80 bg-white border rounded-lg shadow-lg">
                            <div class="p-4 grid grid-cols-1 gap-4">
                                <!-- Main Categories -->
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-2">{{ __('Csapágytípusok') }}</h3>
                                    <ul class="space-y-2">
                                        <li>
                                            <a href="#"
                                                class="text-gray-600 hover:text-blue-600 flex items-center justify-between group">
                                                <span>{{ __('Mélyhornyú golyóscsapágyak') }}</span>
                                                <i
                                                    class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#"
                                                class="text-gray-600 hover:text-blue-600 flex items-center justify-between group">
                                                <span>{{ __('Tűgörgős csapágyak') }}</span>
                                                <i
                                                    class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#"
                                                class="text-gray-600 hover:text-blue-600 flex items-center justify-between group">
                                                <span>{{ __('Hengergörgős csapágyak') }}</span>
                                                <i
                                                    class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Popular Series -->
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-2">{{ __('Népszerű sorozatok') }}</h3>
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="#"
                                            class="text-sm bg-gray-50 hover:bg-gray-100 rounded p-2 text-gray-600 hover:text-blue-600">
                                            {{ __('62XX sorozat') }}
                                        </a>
                                        <a href="#"
                                            class="text-sm bg-gray-50 hover:bg-gray-100 rounded p-2 text-gray-600 hover:text-blue-600">
                                            {{ __('63XX sorozat') }}
                                        </a>
                                        <a href="#"
                                            class="text-sm bg-gray-50 hover:bg-gray-100 rounded p-2 text-gray-600 hover:text-blue-600">
                                            {{ __('60XX sorozat') }}
                                        </a>
                                        <a href="#"
                                            class="text-sm bg-gray-50 hover:bg-gray-100 rounded p-2 text-gray-600 hover:text-blue-600">
                                            {{ __('NK sorozat') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Nav Items -->
                    <a href="#" class="text-gray-700 hover:text-blue-600">{{ __('Márkák') }}</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600">{{ __('Akciók') }}</a>

                    <!-- Cégünkről Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @mouseenter="open = true" @mouseleave="open = false"
                            class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 font-medium">
                            <span>{{ __('Cégünkről') }}</span>
                            <i class="fas fa-chevron-down text-sm transition-transform"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <!-- Cégünkről Dropdown -->
                        <div x-show="open" @mouseenter="open = true" @mouseleave="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 z-10 mt-2 w-64 bg-white border rounded-lg shadow-lg">
                            <div class="py-2">
                                <a href="{{ route('services') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                    {{ __('Szolgáltatásaink') }}
                                </a>
                                <a href="{{ route('team') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                    {{ __('Munkatársaink') }}
                                </a>
                                <a href="{{ route('company-data') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                    {{ __('Cégadatok') }}
                                </a>

                                <!-- Dokumentumok Submenu -->
                                <div class="relative" x-data="{ submenuOpen: false }">
                                    <button @mouseenter="submenuOpen = true" @mouseleave="submenuOpen = false"
                                        class="w-full flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                        <span>{{ __('Dokumentumok') }}</span>
                                        <i class="fas fa-chevron-right text-sm transition-transform"
                                            :class="{ 'rotate-90': submenuOpen }"></i>
                                    </button>

                                    <!-- Dokumentumok Submenu Dropdown -->
                                    <div x-show="submenuOpen" @mouseenter="submenuOpen = true"
                                        @mouseleave="submenuOpen = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-x-1"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-1"
                                        class="absolute left-full top-0 ml-1 w-64 bg-white border rounded-lg shadow-lg">
                                        <div class="py-2">
                                            <a href="{{ route('documents') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                                {{ __('Hirdetmény') }}
                                            </a>
                                            <a href="{{ route('terms-and-conditions') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                                {{ __('Általános Szerződési Feltételek') }}
                                            </a>
                                            <a href="{{ route('delivery-framework') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                                {{ __('Szállítási keretszerződés') }}
                                            </a>
                                            <a href="{{ route('quality-policy') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                                {{ __('Minőségpolitika') }}
                                            </a>
                                            <a href="{{ route('privacy-policy') }}"
                                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                                {{ __('Adatkezelési tájékoztató') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('contact') }}"
                        class="text-gray-700 hover:text-blue-600">{{ __('Kapcsolat') }}</a>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="hidden lg:flex flex-1 max-w-2xl mx-8">
                <form action="/products" method="GET" class="w-full">
                    <div class="relative">
                        <input type="text" name="search"
                            placeholder="{{ __('Keresés termékek között... (pl: 6205-2RS, SKF golyóscsapágy)') }}"
                            class="w-full pl-12 pr-4 py-2 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Side Menu -->
            <div class="flex items-center space-x-4">
                <!-- Cart -->
                <a href="#" class="text-gray-700 hover:text-blue-600 p-2 relative">
                    <i class="fas fa-shopping-cart"></i>
                    <span
                        class="absolute top-0 right-0 -mt-1 -mr-1 bg-blue-600 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                        0
                    </span>
                </a>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden text-gray-700 hover:text-blue-600 p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </nav>

        <!-- Search Overlay -->
        <div x-data x-show="$store.search.isOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="absolute inset-x-0 top-full bg-white border-b shadow-lg z-50">
            <div class="container mx-auto px-4 py-4">
                <form @submit.prevent="$store.search.submit()" class="relative">
                    <div class="relative">
                        <input type="text"
                            placeholder="{{ __('Keresés termékek között... (pl: 6205-2RS, SKF golyóscsapágy)') }}"
                            x-model="$store.search.query" @keydown.escape="$store.search.close()"
                            class="w-full pl-12 pr-4 py-3 text-lg rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-lg"></i>
                        </div>
                        <button type="button" @click="$store.search.close()"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Quick Links -->
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('Népszerű keresések:') }}</h4>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="$store.search.setQuery('{{ __('SKF golyóscsapágy') }}')"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm text-gray-600 transition-colors">
                                {{ __('SKF golyóscsapágy') }}
                            </button>
                            <button type="button" @click="$store.search.setQuery('{{ __('6205 sorozat') }}')"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm text-gray-600 transition-colors">
                                {{ __('6205 sorozat') }}
                            </button>
                            <button type="button" @click="$store.search.setQuery('{{ __('tűgörgős csapágy') }}')"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm text-gray-600 transition-colors">
                                {{ __('tűgörgős csapágy') }}
                            </button>
                            <button type="button" @click="$store.search.setQuery('{{ __('Gumi porvédős') }}')"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm text-gray-600 transition-colors">
                                {{ __('Gumi porvédős') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden">
        <div class="border-t px-4 py-4 space-y-4">
            <!-- Mobile Category Menu -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full py-2 text-gray-700">
                    <span class="font-medium">{{ __('Termékkategóriák') }}</span>
                    <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" class="mt-2 space-y-2 pl-4">
                    <a href="#"
                        class="block py-2 text-gray-600 hover:text-blue-600">{{ __('Mélyhornyú golyóscsapágyak') }}</a>
                    <a href="#"
                        class="block py-2 text-gray-600 hover:text-blue-600">{{ __('Tűgörgős csapágyak') }}</a>
                    <a href="#"
                        class="block py-2 text-gray-600 hover:text-blue-600">{{ __('Hengergörgős csapágyak') }}</a>
                </div>
            </div>

            <a href="#" class="block py-2 text-gray-700 hover:text-blue-600">{{ __('Márkák') }}</a>
            <a href="#" class="block py-2 text-gray-700 hover:text-blue-600">{{ __('Akciók') }}</a>

            <!-- Mobile Cégünkről Menu -->
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full py-2 text-gray-700">
                    <span class="font-medium">{{ __('Cégünkről') }}</span>
                    <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" class="mt-2 space-y-2 pl-4">
                    <a href="{{ route('services') }}"
                        class="block py-2 text-gray-600 hover:text-blue-600">{{ __('Szolgáltatásaink') }}</a>
                    <a href="{{ route('team') }}"
                        class="block py-2 text-gray-600 hover:text-blue-600">{{ __('Munkatársaink') }}</a>
                    <a href="{{ route('company-data') }}"
                        class="block py-2 text-gray-600 hover:text-blue-600">{{ __('Cégadatok') }}</a>

                    <!-- Mobile Dokumentumok Submenu -->
                    <div x-data="{ submenuOpen: false }">
                        <button @click="submenuOpen = !submenuOpen"
                            class="flex items-center justify-between w-full py-2 text-gray-600">
                            <span>{{ __('Dokumentumok') }}</span>
                            <i class="fas fa-chevron-down text-sm transition-transform"
                                :class="{ 'rotate-180': submenuOpen }"></i>
                        </button>

                        <div x-show="submenuOpen" class="mt-2 space-y-2 pl-4">
                            <a href="{{ route('documents') }}"
                                class="block py-2 text-gray-500 hover:text-blue-600">{{ __('Hirdetmény') }}</a>
                            <a href="{{ route('terms-and-conditions') }}"
                                class="block py-2 text-gray-500 hover:text-blue-600">{{ __('Általános Szerződési Feltételek') }}</a>
                            <a href="{{ route('delivery-framework') }}"
                                class="block py-2 text-gray-500 hover:text-blue-600">{{ __('Szállítási keretszerződés') }}</a>
                            <a href="{{ route('quality-policy') }}"
                                class="block py-2 text-gray-500 hover:text-blue-600">{{ __('Minőségpolitika') }}</a>
                            <a href="{{ route('privacy-policy') }}"
                                class="block py-2 text-gray-500 hover:text-blue-600">{{ __('Adatkezelési tájékoztató') }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('contact') }}"
                class="block py-2 text-gray-700 hover:text-blue-600">{{ __('Kapcsolat') }}</a>
        </div>
    </div>
</div>

<!-- Alpine.js Store for Search -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('search', {
            isOpen: false,
            query: '',
            toggle() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.$nextTick(() => {
                        document.querySelector('input[type="text"]').focus();
                    });
                }
            },
            close() {
                this.isOpen = false;
            },
            setQuery(query) {
                this.query = query;
                document.querySelector('input[type="text"]').focus();
            },
            submit() {
                if (this.query.trim()) {
                    window.location.href = `/products?search=${encodeURIComponent(this.query.trim())}`;
                }
            }
        });
    });
</script>
