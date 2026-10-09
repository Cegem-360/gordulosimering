<?php

declare(strict_types=1);

/*
 * The brands shown on the Márkáink page (and the x-brand-logos strip), in
 * this order. "logo" is a file in resources/images/brands/, "height" a
 * Tailwind height that balances the logos optically, "invert" is for white
 * logos. "filter" is the products.brand value the logo links to in the
 * product list (?marka=); brands without products of their own (sold under
 * other names in the ERP) have none and are shown without a link.
 */
return [
    ['name' => 'SKF', 'logo' => 'skf.svg', 'height' => 'h-8', 'filter' => 'SKF'],
    ['name' => 'INA', 'logo' => 'ina.svg', 'height' => 'h-20', 'filter' => 'INA'],
    ['name' => 'FAG', 'logo' => 'fag.svg', 'height' => 'h-7', 'filter' => 'FAG'],
    ['name' => 'TIMKEN', 'logo' => 'timken.png', 'height' => 'h-8', 'filter' => 'TIMKEN'],
    ['name' => 'ZKL/ZVL', 'logo' => 'zkl.svg', 'height' => 'h-10', 'filter' => 'ZKL'],
    ['name' => 'LOCTITE', 'logo' => 'loctite.png', 'height' => 'h-8', 'filter' => 'LOCTITE'],
    ['name' => 'SEEGER', 'logo' => 'seeger.svg', 'height' => 'h-12', 'filter' => 'SEEGER'],
    ['name' => 'NORMA', 'logo' => 'norma.svg', 'height' => 'h-12', 'filter' => 'NORMA'],
    ['name' => 'TENTE', 'logo' => 'tente.png', 'height' => 'h-7', 'filter' => 'TENTE'],
    ['name' => 'BETA', 'logo' => 'beta-logo.png', 'height' => 'h-8', 'filter' => 'BETA'],
    ['name' => 'KS-TOOLS', 'logo' => 'ks-tools.svg', 'height' => 'h-10', 'filter' => 'KS'],
    ['name' => 'BAHCO', 'logo' => 'bahco.svg', 'height' => 'h-24', 'filter' => 'BAHCO'],
    ['name' => 'DURACELL', 'logo' => 'duracell.svg', 'height' => 'h-7', 'invert' => true, 'filter' => 'DURACELL'],
    ['name' => 'ENERGIZER', 'logo' => 'energizer.png', 'height' => 'h-10', 'filter' => null],
    ['name' => 'FABORY', 'logo' => 'fabory.svg', 'height' => 'h-7', 'filter' => null],
    ['name' => 'NICRO', 'logo' => 'nicro-logo.png', 'height' => 'h-8', 'filter' => null],
    ['name' => 'OKS', 'logo' => 'oks.png', 'height' => 'h-12', 'filter' => null],
];
