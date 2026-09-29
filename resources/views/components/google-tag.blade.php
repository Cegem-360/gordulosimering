{{-- Google Consent Mode v2: while the cookie banner is on, every storage type
     starts out denied and the visitor's earlier choice (saved by the
     cookie-consent component) is applied before any Google tag loads. --}}
@php
    $integrations = app(\App\Settings\IntegrationSettings::class);
    $googleAnalyticsId = trim($integrations->google_analytics_id);
    $googleTagManagerId = trim($integrations->google_tag_manager_id);
    $isConsentRequired = app(\App\Settings\CookieConsentSettings::class)->enabled;
@endphp

@if ($googleAnalyticsId !== '' || $googleTagManagerId !== '')
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        @if ($isConsentRequired)
            gtag('consent', 'default', {
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied',
                analytics_storage: 'denied',
                wait_for_update: 500
            });

            (function() {
                try {
                    var choice = JSON.parse(localStorage.getItem('cookie_consent') || 'null');

                    if (choice && choice.categories) {
                        var marketing = choice.categories.marketing ? 'granted' : 'denied';

                        gtag('consent', 'update', {
                            ad_storage: marketing,
                            ad_user_data: marketing,
                            ad_personalization: marketing,
                            analytics_storage: choice.categories.analytics ? 'granted' : 'denied'
                        });
                    }
                } catch (e) {}
            })();
        @endif
    </script>
@endif

@if ($googleTagManagerId !== '')
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', @js($googleTagManagerId));
    </script>
@endif

@if ($googleAnalyticsId !== '')
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($googleAnalyticsId) }}"></script>
    <script>
        gtag('js', new Date());
        gtag('config', @js($googleAnalyticsId));
    </script>
@endif
