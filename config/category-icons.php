<?php

declare(strict_types=1);

/*
 * Which icon the homepage category menu shows for a category, matched by
 * name. The keys are symbol ids in resources/images/category-icons.svg, line
 * icons drawn by hand after the client's AI icon sheet (2026-09-29).
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
            'VEZETŐ- ÉS TÁMASZTÓ GÖRGŐ' => 'roller',
            'SIKLÓ CSAPÁGY / CSÚSZÓ CSAPÁGY' => 'washer',
            'LINEÁRIS CSAPÁGY' => 'linear-rail',
        ],
        'CSAPÁGYHÁZAK' => [
            'CSAPÁGYHÁZ Y CSAPÁGYAKHOZ' => 'pillow-block',
        ],
        'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK' => [
            'GOLYÓ, TŰGÖRGŐ' => 'ball',
            'CSAPÁGYANYA ÉS BIZTOSÍTÓLEMEZ' => 'nut',
            'ZSÍRZÓGOMB' => 'grease-gun',
            'HÉZAGOLÓ ALÁTÉT' => 'washer',
            'KOMPENZÁCIÓS LEMEZ' => 'washer',
            'NILOS GYŰRŰ' => 'o-ring',
            'TÖMÍTŐ ALÁTÉTEK' => 'washer',
        ],
        'TÖMÍTÉSEK' => [
            'SZIMERING' => 'seal',
            'V-GYŰRŰ' => 'o-ring',
            'O-GYŰRŰ' => 'o-ring',
            'TÖMÍTŐ ZSINÓR' => 'o-ring',
            'SZIMERING RUGÓ' => 'seal',
            'SZERELŐ SZERSZÁM TÖMÍTÉSEKHEZ' => 'screwdriver',
        ],
        'HAJTÁSTECHNIKA' => [
            'SZÍJHATÁS' => 'belt-drive',
            'TENGELYKAPCSOLÓ' => 'coupling',
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
            'GOLYÓS BETÉT' => 'ball',
            'BILINCS' => 'hose-clamp',
        ],
        'KEREKEK ÉS GÖRGŐK' => [
            'IPARI GÖRGŐ' => 'roller',
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
            'MUNKAVÉDELMI KESZTYŰ' => 'glove',
        ],
    ],

    'children_without_icons' => ['FORGALMAZOTT MÁRKÁINK'],
];
