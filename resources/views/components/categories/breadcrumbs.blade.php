@props(['items'])

<nav class="bg-gray-100 py-3 px-4">
    <ol class="flex items-center space-x-2 text-sm">
        <li>
            <a href="{{ route('products.index') }}" class="text-blue-600 hover:underline">Kezdőlap</a>
        </li>
        <li class="flex items-center space-x-2">
            <span class="text-gray-500">&gt;</span>
            <a href="{{ route('categories.index') }}" class="text-blue-600 hover:underline">Termékkategóriák</a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center space-x-2">
                <span class="text-gray-500">&gt;</span>
                @if ($loop->last)
                    <span class="text-gray-700">{{ $item['name'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" class="text-blue-600 hover:underline">{{ $item['name'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
