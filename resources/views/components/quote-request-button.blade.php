@props(['product'])

{{-- Ajánlatkérés gomb a termékkártyán és a termékoldalon. Ideiglenesen egy
     előre kitöltött e-mailt nyit a gs@ címre; ha elkészül az ajánlatkérő
     funkció, csak ezt a komponenst kell rákötni. --}}
@php
    $subject = 'Ajánlatkérés: '.collect([$product->name, $product->product_code])->filter()->join(' – ');
    $body = "Tisztelt GÖRDÜLŐ-Simmering Kft.!\n\nAjánlatot kérek az alábbi termékre:\n"
        .collect([$product->name, $product->product_code ? 'Termékkód: '.$product->product_code : null])->filter()->join("\n")
        ."\n".route('products.show', ['product' => $product->slug])
        ."\n\nMennyiség: \n\nKöszönöm!";
    $href = 'mailto:'.config('shop.contact_email').'?subject='.rawurlencode($subject).'&body='.rawurlencode($body);
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center justify-center gap-2']) }}>
    <i class="fa fa-file-signature"></i> Ajánlatkérés
</a>
