@props(['title', 'source'])

{{-- Renders a legal text kept as Markdown in resources/legal/. Every "## "
     heading starts a card; "### " subheadings, lists and links stay inside
     it. The client's documents change rarely and as a whole, so the text
     lives in one file each rather than in the markup. --}}
@php
    $markdown = file_get_contents(resource_path("legal/{$source}.md"));
    $sections = collect(preg_split('/^## /m', $markdown))
        ->map(fn (string $chunk) => trim($chunk))
        ->filter()
        ->map(function (string $chunk): array {
            [$heading, $body] = array_pad(explode("\n", $chunk, 2), 2, '');

            return [
                'heading' => trim($heading),
                'html' => Str::markdown(trim($body), ['html_input' => 'escape', 'allow_unsafe_links' => false]),
            ];
        })
        ->values();
@endphp

<div class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">{{ $title }}</h1>

        <div class="space-y-6">
            @foreach ($sections as $section)
                <section class="bg-white border border-gray-300 rounded-lg shadow-sm p-5">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">{{ $section['heading'] }}</h2>
                    <div
                        class="text-gray-700 space-y-3 [&_h3]:mt-5 [&_h3]:mb-2 [&_h3]:font-semibold [&_h3]:text-gray-900 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-3 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-blue-600 [&_a]:underline [&_a]:break-all">
                        {!! $section['html'] !!}
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
