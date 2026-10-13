<?php

declare(strict_types=1);

/*
 * The brands on the Márkáink page, as the client listed them on 2026-10-13
 * (logó.docx) plus LOCTITE, which they asked back the same day: SKF first,
 * the rest alphabetically. The logo files in
 * resources/images/brands/ are built by __client/brand-logos-sources/build.py
 * (official files where the brand's site has a good one, otherwise the
 * client's own image; see SOURCES.md there).
 *
 * "filter": the products.brand value the logo links to (?marka=).
 * "search": a name search for brands the ERP data has no brand value for.
 * Neither, or no matching product: the logo is shown without a link.
 * "size": "box" for square or boxed logos, which need more height than the
 * wide wordmarks to look as big. "tile" => false: left out of the 30 logos
 * on the homepage Forgalmazott márkáink box (no products in the shop).
 */
return [
    ['name' => 'SKF', 'logo' => 'skf.svg', 'filter' => 'SKF', 'size' => 'box'],
    ['name' => 'AMES', 'logo' => 'ames.webp', 'filter' => 'AMES', 'size' => 'box'],
    ['name' => 'BECO', 'logo' => 'beco.svg', 'filter' => 'BECO'],
    ['name' => 'Beta', 'logo' => 'beta.webp', 'filter' => 'BETA'],
    ['name' => 'CERVA', 'logo' => 'cerva.svg', 'search' => 'CERVA', 'size' => 'box'],
    ['name' => 'CFA', 'logo' => 'cfa.webp', 'size' => 'box', 'tile' => false],
    ['name' => 'EURO ELZETT', 'logo' => 'euro-elzett.webp', 'search' => 'ELZETT'],
    ['name' => 'EZO', 'logo' => 'ezo.webp', 'filter' => 'EZO', 'size' => 'box'],
    ['name' => 'F.BS', 'logo' => 'f-bs.webp', 'search' => 'F.BS'],
    ['name' => 'FAG', 'logo' => 'fag.webp', 'filter' => 'FAG'],
    ['name' => 'INA', 'logo' => 'ina.svg', 'filter' => 'INA', 'size' => 'box'],
    ['name' => 'JWIS', 'logo' => 'jwis.webp', 'search' => 'IWIS'],
    ['name' => 'Koyo', 'logo' => 'koyo.webp', 'filter' => 'KOYO'],
    ['name' => 'LOCTITE', 'logo' => 'loctite.webp', 'filter' => 'LOCTITE', 'size' => 'box'],
    ['name' => 'MEGADYNE', 'logo' => 'megadyne.svg', 'tile' => false],
    ['name' => 'Milwaukee', 'logo' => 'milwaukee.svg', 'search' => 'MILWAUKEE', 'size' => 'box'],
    ['name' => 'Mitutoyo', 'logo' => 'mitutoyo.svg', 'search' => 'MITUTOYO'],
    ['name' => 'MOTIP', 'logo' => 'motip.svg', 'search' => 'MOTIP'],
    ['name' => 'NACHI', 'logo' => 'nachi.webp', 'filter' => 'NACHI'],
    ['name' => 'NSK', 'logo' => 'nsk.svg', 'filter' => 'NSK'],
    ['name' => 'NTN', 'logo' => 'ntn.svg', 'filter' => 'NTN'],
    ['name' => 'OKS', 'logo' => 'oks.webp', 'search' => 'OKS'],
    ['name' => 'optibelt', 'logo' => 'optibelt.svg', 'size' => 'box'],
    ['name' => 'Pattex', 'logo' => 'pattex.svg', 'search' => 'PATTEX', 'size' => 'box'],
    ['name' => 'PERMAGLIDE', 'logo' => 'permaglide.webp', 'search' => 'PERMAGLIDE'],
    ['name' => 'SNR', 'logo' => 'snr.webp', 'filter' => 'SNR', 'size' => 'box'],
    ['name' => 'sola', 'logo' => 'sola.svg', 'search' => 'SOLA'],
    ['name' => 'TENTE', 'logo' => 'tente.svg', 'filter' => 'TENTE'],
    ['name' => 'WD-40', 'logo' => 'wd-40.svg', 'search' => 'WD-40', 'size' => 'box'],
    ['name' => 'WIPPERMANN', 'logo' => 'wippermann.svg', 'search' => 'WIPPERMANN'],
    ['name' => 'YATO', 'logo' => 'yato.svg', 'search' => 'YATO'],
    ['name' => 'ZVL SLOVAKIA', 'logo' => 'zvl.webp', 'search' => 'ZVL'],
];
