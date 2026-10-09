@props(['inList' => false])

{{-- Ajánlatkérés gomb a termékkártyán és a termékoldalon: a terméket az
     ajánlatkérési listára teszi (AddsToQuoteList), utána az Ajánlatkérés
     oldalra visz. --}}
@if ($inList)
    <a href="{{ route('quote-request') }}" {{ $attributes->merge(['class' => 'flex items-center justify-center gap-2']) }}>
        <i class="fa fa-check"></i> Ajánlatkérésben
    </a>
@else
    <button type="button" wire:click="addToQuote" wire:loading.attr="disabled" wire:target="addToQuote"
        {{ $attributes->merge(['class' => 'flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60']) }}>
        <i class="fa fa-file-signature"></i> Ajánlatkérés
    </button>
@endif
