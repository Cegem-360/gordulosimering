{{-- Appears once the visitor has scrolled a screen's worth down the page and
     scrolls smoothly back to the top. --}}
<button type="button" x-data="{ shown: false }" x-cloak x-show="shown"
    @scroll.window.throttle.100ms="shown = window.scrollY > window.innerHeight"
    x-init="shown = window.scrollY > window.innerHeight"
    @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
    x-transition.opacity.duration.200ms
    class="fixed bottom-6 right-6 z-40 flex items-center justify-center w-12 h-12 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 transition-colors"
    aria-label="Vissza az oldal tetejére" title="Vissza az oldal tetejére">
    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" />
    </svg>
</button>
