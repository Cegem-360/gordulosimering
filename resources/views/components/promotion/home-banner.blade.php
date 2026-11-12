@inject('promotion', 'App\Settings\PromotionSettings')

{{-- Set in the admin (Beállítások > Sáv és banner). Phones get their own
     (square) image when one was uploaded. Off: the homepage is unchanged. --}}
@if ($promotion->showsBanner())
    <div class="container mx-auto px-4 pt-4" data-home-banner>
        @if ($promotion->banner_url)
            <a href="{{ $promotion->banner_url }}" class="block">
        @endif
        <picture>
            <source media="(max-width: 767px)" srcset="{{ $promotion->bannerMobileUrl() }}">
            <img src="{{ $promotion->bannerDesktopUrl() }}" alt="{{ $promotion->banner_alt ?? '' }}"
                class="w-full h-auto rounded-lg shadow-md">
        </picture>
        @if ($promotion->banner_url)
            </a>
        @endif
    </div>
@endif
