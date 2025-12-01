<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumbs -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center space-x-2 text-sm">
                <a href="/" class="text-blue-600 hover:underline">Kezdőlap</a>
                <span class="text-gray-500">&gt;</span>
                <span class="text-gray-700">Profilom</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="flex items-center gap-3 mb-8">
            <i class="fas fa-user-circle text-3xl text-gray-400"></i>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Profilom</h1>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column: Profile Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Profile Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                    <form wire:submit="updateProfile">
                        {{ $this->form }}

                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium transition-colors">
                                Adatok mentése
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-lg font-bold mb-4 pb-3 border-b">Jelszó módosítása</h2>

                    <form wire:submit="updatePassword" class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Jelenlegi jelszó
                            </label>
                            <input type="password" id="current_password" wire:model="current_password"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Új jelszó
                            </label>
                            <input type="password" id="new_password" wire:model="new_password"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                            @error('new_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Új jelszó megerősítése
                            </label>
                            <input type="password" id="new_password_confirmation"
                                wire:model="new_password_confirmation"
                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                                Jelszó módosítása
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Quick Links -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Quick Links Card -->
                <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-lg font-bold mb-4 pb-3 border-b">Gyors linkek</h2>

                    <div class="space-y-3">
                        <a href="{{ route('orders.history') }}"
                            class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-colors">
                            <i class="fas fa-shopping-bag text-blue-600 text-lg"></i>
                            <div>
                                <p class="font-medium text-gray-900">Rendeléseim</p>
                                <p class="text-sm text-gray-500">Korábbi rendelések megtekintése</p>
                            </div>
                        </a>

                        <a href="{{ route('cart') }}"
                            class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-colors">
                            <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                            <div>
                                <p class="font-medium text-gray-900">Kosár</p>
                                <p class="text-sm text-gray-500">Aktuális kosár tartalma</p>
                            </div>
                        </a>

                        <a href="{{ route('categories.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-colors">
                            <i class="fas fa-th-large text-blue-600 text-lg"></i>
                            <div>
                                <p class="font-medium text-gray-900">Termékkategóriák</p>
                                <p class="text-sm text-gray-500">Böngészés a termékek között</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Account Info Card -->
                <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-lg font-bold mb-4 pb-3 border-b">Fiók információ</h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Email cím</dt>
                            <dd class="font-medium text-gray-900">{{ Auth::user()->email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Regisztráció dátuma</dt>
                            <dd class="font-medium text-gray-900">{{ Auth::user()->created_at->format('Y. m. d.') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white rounded-lg border border-red-200 p-6 shadow-sm">
                    <h2 class="text-lg font-bold mb-4 pb-3 border-b border-red-200 text-red-600">Veszélyes zóna</h2>

                    <p class="text-sm text-gray-600 mb-4">
                        Ha törölni szeretnéd a fiókodat, kérjük vedd fel velünk a kapcsolatot.
                    </p>

                    <a href="{{ route('contact') }}"
                        class="inline-flex items-center gap-2 text-sm text-red-600 hover:text-red-800 font-medium">
                        <i class="fas fa-envelope"></i>
                        Kapcsolatfelvétel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />
</div>
