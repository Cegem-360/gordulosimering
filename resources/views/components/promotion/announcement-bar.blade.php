@inject('promotion', 'App\Settings\PromotionSettings')

{{-- Set in the admin (Beállítások > Sáv és banner). Off or empty: nothing is
     rendered, so the header stays at the top of the page. It scrolls away with
     the page, so on phones it never covers the sticky menu and cart. --}}
@if ($promotion->showsAnnouncement())
    @php
        $tag = $promotion->announcement_url ? 'a' : 'div';
    @endphp
    <{{ $tag }} @if ($promotion->announcement_url) href="{{ $promotion->announcement_url }}" @endif
        data-announcement-bar
        @class(['block w-full px-4 py-2 text-center text-sm font-medium leading-snug', 'hover:underline' => $promotion->announcement_url])
        style="background-color: {{ $promotion->announcement_color }}; color: {{ $promotion->announcementTextColor() }};">
        {{ $promotion->announcement_text }}
    </{{ $tag }}>
@endif
