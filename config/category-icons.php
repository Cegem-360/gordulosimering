<?php

declare(strict_types=1);

/*
 * Which icon the homepage category menu shows for a category, matched by
 * name. The icons are resources/images/category-icons/<key>.svg, traced from
 * the client's icon sheet (sent 2026-09-29) by a potrace script kept locally
 * in the gitignored __client/ folder.
 *
 * "roots": the main categories. "children": first-level subcategories, keyed
 * by their parent's name; a subcategory that is not listed gets its parent's
 * icon. Deeper levels get no icon, and neither do the children listed in
 * "children_without_icons" (the 37 brand names). A category that is missing
 * here (renamed, new) falls back to "default".
 */
return [
    'default' => null,

    'roots' => [
        'CSAPÁGYAK' => 'bearing',
        'CSAPÁGYEGYSÉGEK' => 'pillow-block',
        'CSAPÁGYHÁZAK' => 'flange-housing',
        'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK' => 'circlip',
        'LINEÁRTECHNIKA' => 'linear-rail',
        'TÖMÍTÉSEK' => 'seal',
        'HAJTÁSTECHNIKA' => 'gear',
        'VEGYI ÁRUK' => 'canister',
        'ZSÍRZÁSTECHNIKA' => 'grease-gun',
        'KÖTŐELEMEK, GÉPÉPÍTŐ ELEMEK' => 'bolt',
        'KEREKEK ÉS GÖRGŐK' => 'caster',
        'KÉZISZERSZÁMOK ÉS MŰSZEREK' => 'wrench',
        'BILINCSEK' => 'hose-clamp',
        'ELEMEK, AKKUMULÁTOROK' => 'battery',
        'MUNKAVÉDELMI CIPŐ, KESZTYŰ' => 'boot',
        'FORGALMAZOTT MÁRKÁINK' => 'brand-tag',
    ],

    'children' => [
        'CSAPÁGYAK' => [
            'GOLYÓS CSAPÁGY' => 'ball-bearing-small',
            'VEZETŐ- ÉS TÁMASZTÓ GÖRGŐ' => 'roller-wheel',
            'SIKLÓ CSAPÁGY / CSÚSZÓ CSAPÁGY' => 'washer',
            'LINEÁRIS CSAPÁGY' => 'linear-rail',
        ],
        'CSAPÁGYHÁZAK' => [
            'CSAPÁGYHÁZ Y CSAPÁGYAKHOZ' => 'pillow-block',
        ],
        'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK' => [
            'GOLYÓ, TŰGÖRGŐ' => 'ball-bearing-small',
            'CSAPÁGYANYA ÉS BIZTOSÍTÓLEMEZ' => 'hex-nut',
            'ZSÍRZÓGOMB' => 'grease-gun',
            'HÉZAGOLÓ ALÁTÉT' => 'washer',
            'KOMPENZÁCIÓS LEMEZ' => 'washer',
            'NILOS GYŰRŰ' => 'seal-ring-small',
            'TÖMÍTŐ ALÁTÉTEK' => 'washer',
        ],
        'TÖMÍTÉSEK' => [
            'SZIMERING' => 'shaft-seal-large',
            'V-GYŰRŰ' => 'seal-ring-small',
            'O-GYŰRŰ' => 'o-ring',
            'TÖMÍTŐ ZSINÓR' => 'o-ring',
            'SZIMERING RUGÓ' => 'shaft-seal-large',
            'SZERELŐ SZERSZÁM TÖMÍTÉSEKHEZ' => 'screwdriver',
        ],
        'HAJTÁSTECHNIKA' => [
            'SZÍJHATÁS' => 'motor',
            'TENGELYKAPCSOLÓ' => 'motor',
        ],
        'VEGYI ÁRUK' => [
            'CSAVARRÖGZÍTŐ' => 'bolt',
            'CSAPÁGYRÖGZÍTŐ' => 'bearing',
            'BERÁGÓDÁSGÁTLÓ' => 'spray-can',
            'TISZTÍTÁS, ZSÍRTALANÍTÁS' => 'spray-can',
            'AKTIVÁTOR, PRIMER' => 'spray-can',
            'ZSÍR, OLAJ' => 'grease-tub',
            'KARBANTARTÁSI TERMÉKEK' => 'spray-can',
            'ADAGOLÓ ESZKÖZ' => 'grease-gun',
        ],
        'KÖTŐELEMEK, GÉPÉPÍTŐ ELEMEK' => [
            'ALÁTÉT' => 'washer',
            'GOLYÓS BETÉT' => 'ball-bearing-small',
            'BILINCS' => 'hose-clamp',
        ],
        'KEREKEK ÉS GÖRGŐK' => [
            'IPARI GÖRGŐ' => 'roller-wheel',
        ],
        'KÉZISZERSZÁMOK ÉS MŰSZEREK' => [
            'MÉRŐ- ÉS ELLENŐRZŐ MŰSZEREK' => 'caliper',
            'MÉRŐESZKÖZÖK, MŰSZEREK' => 'caliper',
            'SZERELÉSTECHNIKAI ESZKÖZÖK, ALKATRÉSZEK' => 'screwdriver',
            'CSAPÁGYMELEGÍTŐ' => 'bearing',
            'EGYÉB KÉZISZERSZÁM' => 'screwdriver',
        ],
        'MUNKAVÉDELMI CIPŐ, KESZTYŰ' => [
            'MUNKAVÉDELMI CIPŐ' => 'boot',
        ],
    ],

    'children_without_icons' => ['FORGALMAZOTT MÁRKÁINK'],
];
