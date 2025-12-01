<a href="{{ route('cart') }}" class="flex items-center gap-2 text-gray-700 hover:text-blue-600 p-2 relative group">
    <div class="relative">
        <i class="fas fa-shopping-cart text-lg"></i>
        @if ($itemCount > 0)
            <span class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                {{ $itemCount > 99 ? '99+' : $itemCount }}
            </span>
        @endif
    </div>
    @if ($total > 0)
        <span class="hidden md:inline text-sm font-medium">
            {{ number_format($total, 0, ',', ' ') }} Ft
        </span>
    @endif
</a>
