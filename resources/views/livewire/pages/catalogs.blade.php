<div>
    <div class="bg-linear-to-r from-blue-900 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Katalógusok</h1>
                <p class="text-xl text-blue-100">Termékkatalógusaink és beszállítóink katalógusai letölthető formában.</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        @if ($catalogs->isEmpty())
            <div class="max-w-2xl mx-auto bg-white border border-gray-300 rounded-lg shadow-sm p-8 text-center">
                <i class="fas fa-book-open text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-700">
                    Katalógusainkat hamarosan itt is letöltheti. Addig kérje e-mailben:
                    <a href="mailto:{{ config('shop.contact_email') }}" class="text-blue-600 hover:underline">{{ config('shop.contact_email') }}</a>
                </p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($catalogs as $catalog)
                    <a wire:key="catalog-{{ $catalog->id }}" href="{{ $catalog->fileUrl() }}" target="_blank" rel="noopener"
                        class="group bg-white border border-gray-300 rounded-lg shadow-sm p-5 flex gap-4 hover:shadow-lg hover:border-blue-400 transition">
                        <i class="fas fa-file-pdf text-4xl text-red-600 shrink-0"></i>
                        <div class="min-w-0">
                            <h2 class="font-semibold text-gray-900 group-hover:text-blue-600">{{ $catalog->title }}</h2>
                            @if (filled($catalog->description))
                                <p class="text-sm text-gray-600 mt-1">{{ $catalog->description }}</p>
                            @endif
                            <span class="inline-flex items-center gap-1 text-sm text-blue-600 mt-2">
                                <i class="fas fa-download text-xs"></i> Letöltés (PDF)
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
