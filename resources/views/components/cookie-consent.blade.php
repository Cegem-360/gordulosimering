{{-- The cookie banner, worded and categorised from the admin's Cookie Consent
     settings. The choice is kept in localStorage under "cookie_consent" (the
     google-tag component reads it back on the next page) and is passed on to
     Google Consent Mode. The footer's "Süti beállítások" link reopens it. --}}
@php
    $cookieConsent = app(\App\Settings\CookieConsentSettings::class);
    $categories = collect($cookieConsent->categories)
        ->filter(fn (array $category): bool => filled($category['key'] ?? null))
        ->map(fn (array $category): array => [
            'key' => $category['key'],
            'name' => $category['name'] ?? $category['key'],
            'description' => $category['description'] ?? '',
            'required' => (bool) ($category['required'] ?? false),
        ])
        ->values();
@endphp

@if ($cookieConsent->enabled)
    <div x-data="{
        shown: false,
        showsDetails: false,
        categories: @js($categories),
        selected: {},
        init() {
            let saved = null;

            try {
                saved = JSON.parse(localStorage.getItem('cookie_consent') || 'null');
            } catch (e) {}

            this.categories.forEach((category) => {
                this.selected[category.key] = category.required || Boolean(saved?.categories?.[category.key]);
            });

            this.shown = saved === null;
        },
        open() {
            this.showsDetails = true;
            this.shown = true;
        },
        acceptAll() {
            this.categories.forEach((category) => this.selected[category.key] = true);
            this.save();
        },
        rejectAll() {
            this.categories.forEach((category) => this.selected[category.key] = category.required);
            this.save();
        },
        save() {
            const choice = { categories: { ...this.selected }, savedAt: new Date().toISOString() };

            try {
                localStorage.setItem('cookie_consent', JSON.stringify(choice));
            } catch (e) {}

            if (typeof window.gtag === 'function') {
                const marketing = choice.categories.marketing ? 'granted' : 'denied';

                window.gtag('consent', 'update', {
                    ad_storage: marketing,
                    ad_user_data: marketing,
                    ad_personalization: marketing,
                    analytics_storage: choice.categories.analytics ? 'granted' : 'denied',
                });
            }

            this.shown = false;
            this.showsDetails = false;
        },
    }" x-cloak x-show="shown" x-transition.opacity.duration.200ms @open-cookie-settings.window="open()"
        role="dialog" aria-modal="false" aria-labelledby="cookie-consent-title"
        class="fixed inset-x-0 bottom-0 z-50 p-4 sm:p-6">
        <div class="mx-auto max-w-3xl rounded-lg bg-white p-6 shadow-2xl ring-1 ring-black/10">
            <h2 id="cookie-consent-title" class="text-lg font-semibold text-gray-900">{{ $cookieConsent->title }}</h2>

            <p class="mt-2 text-sm text-gray-600">
                {{ $cookieConsent->description }}
                <a href="{{ route('privacy-policy') }}" class="text-blue-600 hover:underline">Adatvédelmi nyilatkozat</a>
            </p>

            <div x-show="showsDetails" class="mt-4 flex flex-col gap-3">
                <template x-for="category in categories" :key="category.key">
                    <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                        <input type="checkbox" x-model="selected[category.key]" :disabled="category.required"
                            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-60">
                        <span>
                            <span class="block font-medium text-gray-900" x-text="category.name"></span>
                            <span class="block text-sm text-gray-600" x-text="category.description"></span>
                        </span>
                    </label>
                </template>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-show="!showsDetails" @click="showsDetails = true"
                    class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    {{ $cookieConsent->settings_button_text }}
                </button>
                <button type="button" x-show="showsDetails" @click="save()"
                    class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Kiválasztottak mentése
                </button>
                <button type="button" @click="rejectAll()"
                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ $cookieConsent->reject_button_text }}
                </button>
                <button type="button" @click="acceptAll()"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    {{ $cookieConsent->accept_button_text }}
                </button>
            </div>
        </div>
    </div>
@endif
