<?php

declare(strict_types=1);

/*
 * The brands on the Márkáink page, as the client listed them on 2026-10-13
 * (logó.docx), LOCTITE, which they asked back the same day, and their last
 * additions of 2026-10-14 (logok.docx): SKF first, the rest alphabetically. The logo files in
 * resources/images/brands/ are built by __client/brand-logos-sources/build.py
 * (official files where the brand's site has a good one, otherwise the
 * client's own image; see SOURCES.md there).
 *
 * "filter": the products.brand value the logo links to (?marka=).
 * "name_word": for brands the ERP data has no brand value for, the word the
 * product names carry the brand by ("CFA körömvirágos kézkrém", "SKF/LINCOLN
 * zsírzó"); only a whole word of the name counts, never the product code or
 * the size, so "UCFA 205_KBS" is no CFA product (the client's wish, 2026-10-26).
 * "code_part": for KBS, whose names say only "KELETI", the part of the
 * product code that marks it ("_KBS").
 * None of them, or no matching product: the logo is shown without a link.
 * "size": "box" for square or boxed logos, which need more height than the
 * wide wordmarks to look as big. "tile" => false: never among the logos
 * on the homepage Forgalmazott márkáink box (no products in the shop).
 */
return [
    ['name' => 'SKF', 'logo' => 'skf.svg', 'filter' => 'SKF', 'size' => 'box'],
    ['name' => 'AMES', 'logo' => 'ames.webp', 'filter' => 'AMES', 'size' => 'box'],
    ['name' => 'BECO', 'logo' => 'beco.webp', 'filter' => 'BECO'],
    ['name' => 'Beta', 'logo' => 'beta.webp', 'filter' => 'BETA'],
    ['name' => 'CERVA', 'logo' => 'cerva.svg', 'name_word' => 'CERVA', 'size' => 'box'],
    ['name' => 'CFA', 'logo' => 'cfa.webp', 'name_word' => 'CFA', 'size' => 'box'],
    ['name' => 'CORTECO', 'logo' => 'corteco.svg', 'filter' => 'CORTECO'],
    ['name' => 'CX80', 'logo' => 'cx80.webp', 'name_word' => 'CX80', 'size' => 'box'],
    ['name' => 'EURO ELZETT', 'logo' => 'euro-elzett.webp', 'name_word' => 'ELZETT'],
    ['name' => 'EWELLIX', 'logo' => 'ewellix.svg', 'name_word' => 'EWELLIX'],
    ['name' => 'EZO', 'logo' => 'ezo.webp', 'filter' => 'EZO', 'size' => 'box'],
    ['name' => 'F.BS', 'logo' => 'f-bs.webp', 'name_word' => 'F.BS'],
    ['name' => 'FABORY', 'logo' => 'fabory.webp', 'tile' => false],
    ['name' => 'FAG', 'logo' => 'fag.webp', 'filter' => 'FAG'],
    ['name' => 'IKO', 'logo' => 'iko.svg', 'filter' => 'IKO'],
    ['name' => 'INA', 'logo' => 'ina.svg', 'filter' => 'INA', 'size' => 'box'],
    ['name' => 'JWIS', 'logo' => 'jwis.webp', 'name_word' => 'IWIS'],
    ['name' => 'KBS LLC', 'logo' => 'kbs.webp', 'code_part' => '_KBS', 'size' => 'box'],
    ['name' => 'Koyo', 'logo' => 'koyo.webp', 'filter' => 'KOYO', 'size' => 'box'],
    ['name' => 'KS TOOLS', 'logo' => 'ks-tools.svg', 'filter' => 'KS'],
    ['name' => 'LINCOLN', 'logo' => 'lincoln.webp', 'name_word' => 'LINCOLN', 'size' => 'box'],
    ['name' => 'LOCTITE', 'logo' => 'loctite.webp', 'filter' => 'LOCTITE', 'size' => 'box'],
    ['name' => 'MEGADYNE', 'logo' => 'megadyne.svg', 'tile' => false],
    ['name' => 'Milwaukee', 'logo' => 'milwaukee.svg', 'name_word' => 'MILWAUKEE', 'size' => 'box'],
    ['name' => 'Mitutoyo', 'logo' => 'mitutoyo.svg', 'name_word' => 'MITUTOYO'],
    ['name' => 'MOTIP', 'logo' => 'motip.svg', 'name_word' => 'MOTIP'],
    ['name' => 'NACHI', 'logo' => 'nachi.webp', 'filter' => 'NACHI'],
    ['name' => 'NSK', 'logo' => 'nsk.svg', 'filter' => 'NSK'],
    ['name' => 'NTN', 'logo' => 'ntn.webp', 'filter' => 'NTN', 'size' => 'box'],
    ['name' => 'OKS', 'logo' => 'oks.webp', 'name_word' => 'OKS'],
    ['name' => 'optibelt', 'logo' => 'optibelt.webp', 'size' => 'box'],
    ['name' => 'Pattex', 'logo' => 'pattex.webp', 'name_word' => 'PATTEX', 'size' => 'box'],
    ['name' => 'PERMAGLIDE', 'logo' => 'permaglide.webp', 'name_word' => 'PERMAGLIDE'],
    ['name' => 'Rexroth', 'logo' => 'rexroth.svg', 'filter' => 'REXROTH', 'size' => 'box'],
    ['name' => 'Robur', 'logo' => 'robur.webp', 'tile' => false],
    ['name' => 'simrit', 'logo' => 'simrit.webp', 'filter' => 'SIMRIT'],
    ['name' => 'SNR', 'logo' => 'snr.webp', 'filter' => 'SNR', 'size' => 'box'],
    ['name' => 'sola', 'logo' => 'sola.svg', 'name_word' => 'SOLA'],
    ['name' => 'TENTE', 'logo' => 'tente.svg', 'filter' => 'TENTE'],
    ['name' => 'TEROSON', 'logo' => 'teroson.webp', 'name_word' => 'TEROSON'],
    ['name' => 'THK', 'logo' => 'thk.svg', 'name_word' => 'THK'],
    ['name' => 'WD-40', 'logo' => 'wd-40.webp', 'name_word' => 'WD-40', 'size' => 'box'],
    ['name' => 'WIPPERMANN', 'logo' => 'wippermann.svg', 'name_word' => 'WIPPERMANN'],
    ['name' => 'YATO', 'logo' => 'yato.svg', 'name_word' => 'YATO'],
    ['name' => 'ZVL SLOVAKIA', 'logo' => 'zvl.webp', 'name_word' => 'ZVL'],
];
