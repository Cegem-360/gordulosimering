@props(['product', 'quantity' => 1, 'size' => 'md'])

@php
    $sizes = [
        'sm' => ['main' => 'text-sm font-semibold', 'old' => 'text-xs', 'badge' => 'text-[10px] px-1'],
        'md' => ['main' => 'text-xl font-bold', 'old' => 'text-sm', 'badge' => 'text-xs px-1.5'],
        'lg' => ['main' => 'text-3xl md:text-4xl font-bold', 'old' => 'text-lg', 'badge' => 'text-sm px-2'],
    ][$size];
@endphp

<div {{ $attributes->class('flex flex-wrap items-baseline gap-x-2 gap-y-1') }}>
    @if ($product->isOnSale() && (float) $product->net_selling_price > 0)
        <span class="{{ $sizes['old'] }} text-gray-500 line-through">
            {{ Number::currency((float) $product->net_selling_price * $quantity, 'HUF', 'hu', 0) }}
        </span>
        <span class="{{ $sizes['main'] }} text-red-600">
            {{ Number::currency($product->unit_price * $quantity, 'HUF', 'hu', 0) }}
        </span>
        <span class="{{ $sizes['badge'] }} rounded bg-red-600 py-0.5 font-bold text-white">
            -{{ rtrim(rtrim(number_format($product->effective_sale_percentage, 2, ',', ''), '0'), ',') }}%
        </span>
    @else
        <span class="{{ $sizes['main'] }} text-blue-600">
            {{ Number::currency($product->unit_price * $quantity, 'HUF', 'hu', 0) }}
        </span>
    @endif
</div>
