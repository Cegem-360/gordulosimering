@props(['product'])

<div
    class="group bg-white hover:bg-gray-100 hover:shadow-xl transition-all border border-gray-400 rounded-lg p-4 flex flex-col h-full">
    <div class="relative mb-4">
        <img src="{{ $product->main_image ?? 'https://placehold.co/200' }}"
            alt="{{ $product->item_name ?? 'Nincs termék név' }}" class="w-full h-40 object-contain">
        @if ($product->in_stock ?? false)
            <span
                class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-bold flex items-center gap-1">
                <i class="fa fa-cube"></i> Készleten
            </span>
        @endif
    </div>

    <a href="#" class="block mb-4 hover:underline text-blue-600 font-semibold grow">
        {{ $product->item_name ?? 'Nincs termék név' }}
    </a>

    <div class="text-sm font-medium mb-2">{{ $product->manufacturer->name ?? 'Nincs gyártó' }}</div>
    <div class="text-xl font-bold text-blue-600 mb-4">
        {{ number_format($product->net_retail_price ?? 0, 0, ',', ' ') }} Ft
    </div>
    @if ($product->all_quantity > 0 && $product->min_order_quantity <= $product->all_quantity)
        <button type="button"
            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 flex items-center justify-center gap-2">
            <i class="fa fa-cart-plus"></i> Rendelés
        </button>
    @else
        <button type="button"
            class="w-full bg-gray-500 text-white py-2 rounded hover:bg-gray-600 flex items-center justify-center gap-2">
            <i class="fa fa-phone"></i> Hívjon
        </button>
    @endif
</div>
