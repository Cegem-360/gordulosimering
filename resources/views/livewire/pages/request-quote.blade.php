<div>
    <div class="bg-linear-to-r from-blue-900 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Ajánlatkérés</h1>
                <p class="text-xl text-blue-100">Küldje el, mire van szüksége, kollégánk hamarosan jelentkezik ajánlatunkkal.</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        @if ($submittedReference)
            <div class="max-w-2xl mx-auto bg-white border border-gray-300 rounded-lg shadow-sm p-10 text-center">
                <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-700 text-2xl">
                    <i class="fa fa-check"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-3">Köszönjük ajánlatkérését!</h2>
                <p class="text-gray-700">
                    Megkaptuk, és hamarosan jelentkezünk a megadott elérhetőségein. Visszaigazolást küldtünk e-mailben.
                </p>
                <p class="mt-4 text-gray-600">Azonosító: <strong class="text-gray-900">{{ $submittedReference }}</strong></p>
                <a href="{{ route('categories.index') }}"
                    class="mt-8 inline-block text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg px-5 py-2.5">
                    Vissza a termékekhez
                </a>
            </div>
        @else
            <div class="grid lg:grid-cols-2 gap-8 items-start">
                <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-5">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Termékek az ajánlatkérésben <span class="text-gray-400">({{ $lines->count() }})</span>
                    </h2>

                    @if ($lines->isEmpty())
                        <p class="text-gray-700">
                            Az ajánlatkérés még üres. A termékeket a termékkártyák és a termékoldalak
                            „Ajánlatkérés” gombjával teheti bele, vagy egyszerűen írja le az üzenetben, mire van szüksége.
                        </p>
                        <a href="{{ route('categories.index') }}" class="mt-4 inline-block text-blue-600 hover:underline font-medium">
                            Termékek böngészése &rarr;
                        </a>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($lines as $line)
                                @php($product = $line['product'])
                                <li wire:key="quote-line-{{ $product->id }}" class="flex items-center gap-4 py-3">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('products.show', $product) }}" class="block font-semibold text-blue-600 hover:underline">
                                            {{ $product->name }}
                                        </a>
                                        @if (filled($product->product_code))
                                            <div class="text-sm text-gray-600">{{ $product->product_code }}</div>
                                        @endif
                                    </div>

                                    <div class="flex flex-none items-center gap-1">
                                        <button type="button" aria-label="Kevesebb"
                                            wire:click="updateQuantity({{ $product->id }}, {{ max($product->minimumOrderQuantity(), $line['quantity'] - $product->orderQuantityStep()) }})"
                                            class="h-8 w-8 rounded border border-gray-300 bg-gray-100 hover:bg-gray-200 cursor-pointer">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span class="w-12 text-center font-semibold">{{ $line['quantity'] }}</span>
                                        <button type="button" aria-label="Több"
                                            wire:click="updateQuantity({{ $product->id }}, {{ $line['quantity'] + $product->orderQuantityStep() }})"
                                            class="h-8 w-8 rounded border border-gray-300 bg-gray-100 hover:bg-gray-200 cursor-pointer">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                        <span class="ml-1 text-sm text-gray-600">{{ mb_trim((string) $product->quantity_unit) ?: 'db' }}</span>
                                    </div>

                                    <button type="button" aria-label="Eltávolítás" wire:click="removeItem({{ $product->id }})"
                                        class="flex-none p-1 text-gray-400 hover:text-red-600 cursor-pointer">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-5">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Elérhetőségei</h2>
                    <form wire:submit="submit">
                        {{ $this->form }}

                        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="mt-6 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg px-5 py-2.5 disabled:opacity-60 cursor-pointer">
                            <span wire:loading.remove wire:target="submit">Ajánlatkérés elküldése</span>
                            <span wire:loading wire:target="submit">Küldés...</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</div>
