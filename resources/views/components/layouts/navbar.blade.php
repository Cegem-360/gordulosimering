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
                        <a href="{{ route('categories.index') }}" @mouseenter="open = true" @mouseleave="open = false"
                            class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 font-medium">
                            <span>{{ __('Termékkategóriák') }}</span>
                            <i class="fas fa-chevron-down text-sm transition-transform"
                                :class="{ 'rotate-180': open }"></i>
                        </a>

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
                <livewire:live-search />
            </div>

            <!-- Right Side Menu -->
            <div class="flex items-center space-x-4">
                <!-- Auth Links -->
                @auth
                    <div class="relative hidden lg:block" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs transition-transform"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute right-0 z-50 mt-2 w-48 bg-white border rounded-lg shadow-lg">
                            <div class="py-2">
                                <a href="{{ route('profile') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                    <i class="fas fa-user mr-2"></i>
                                    {{ __('Profilom') }}
                                </a>
                                <a href="{{ route('orders.history') }}"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                    <i class="fas fa-shopping-bag mr-2"></i>
                                    {{ __('Rendeléseim') }}
                                </a>
                                <hr class="my-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                                        <i class="fas fa-sign-out-alt mr-2"></i>
                                        {{ __('Kijelentkezés') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden lg:flex items-center text-gray-700 hover:text-blue-600">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        <span class="text-sm font-medium">{{ __('Bejelentkezés') }}</span>
                    </a>
                @endauth

                <!-- Cart -->
                <livewire:cart-icon />

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden text-gray-700 hover:text-blue-600 p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </nav>

    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden">
        <div class="border-t px-4 py-4 space-y-4">
            <!-- Mobile Category Menu -->
            <div x-data="{ open: false }">
                <div class="flex items-center justify-between w-full py-2 text-gray-700">
                    <a href="{{ route('categories.index') }}" class="font-medium hover:text-blue-600">{{ __('Termékkategóriák') }}</a>
                    <button @click="open = !open" class="p-2 -mr-2">
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': open }"></i>
                    </button>
                </div>

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

            <!-- Mobile Auth Links -->
            <div class="border-t pt-4 mt-4">
                @auth
                    <div class="flex items-center py-2 text-gray-700">
                        <i class="fas fa-user-circle text-xl mr-2"></i>
                        <span class="font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <a href="{{ route('profile') }}"
                        class="block py-2 text-gray-600 hover:text-blue-600 pl-7">
                        <i class="fas fa-user mr-2"></i>
                        {{ __('Profilom') }}
                    </a>
                    <a href="{{ route('orders.history') }}"
                        class="block py-2 text-gray-600 hover:text-blue-600 pl-7">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        {{ __('Rendeléseim') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left py-2 text-gray-600 hover:text-blue-600 pl-7">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            {{ __('Kijelentkezés') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="block py-2 text-gray-700 hover:text-blue-600">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        {{ __('Bejelentkezés') }}
                    </a>
                    <a href="{{ route('register') }}"
                        class="block py-2 text-gray-700 hover:text-blue-600">
                        <i class="fas fa-user-plus mr-2"></i>
                        {{ __('Regisztráció') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
