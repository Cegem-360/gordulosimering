<?php

declare(strict_types=1);

return [
    'column_toggle' => [
        'heading' => 'Oszlopok',
    ],
    'columns' => [
        'editable' => [
            'saving' => 'Módosítás mentése...',
            'saved' => 'Módosítás mentve.',
            'failure' => 'A mentés nem erősíthető meg. Frissítse az oldalt, mielőtt újra próbálkozik.',
            'unavailable' => 'Ez az elem már nem szerkeszthető.',
        ],
        'actions' => [
            'label' => 'Művelet|Műveletek',
        ],
        'text' => [
            'actions' => [
                'collapse_list' => ':count elemmel kevesebb mutatása',
                'expand_list' => ':count elemmel több mutatása',
            ],
            'more_list_items' => 'és :count több',
        ],
        'icon' => [
            'boolean' => [
                'true' => 'Igen',
                'false' => 'Nem',
            ],
        ],
        'select' => [
            'loading_message' => 'Betöltés...',
            'no_options_message' => 'Nincs elérhető lehetőség.',
            'no_search_results_message' => 'Egyetlen lehetőség sem illeszkedik a keresésre.',
            'placeholder' => 'Válassz egy lehetőséget',
            'searching_message' => 'Keresés...',
            'search_prompt' => 'Kezdj el írni a kereséshez...',
        ],
    ],
    'fields' => [
        'bulk_select_page' => [
            'label' => 'Az összes elem kijelölése vagy a kijelölés megszüntetése csoportos műveletekhez.',
        ],
        'bulk_select_record' => [
            'label' => ':key elem kijelölése vagy a kijelölés megszüntetése csoportos műveletekhez.',
        ],
        'bulk_select_group' => [
            'label' => ':title csoport kijelölése vagy a kijelölés megszüntetése csoportos műveletekhez.',
        ],
        'search' => [
            'label' => 'Keresés',
            'placeholder' => 'Keresés',
            'indicator' => 'Keresés',
        ],
    ],
    'summary' => [
        'heading' => 'Összesítés',
        'subheadings' => [
            'all' => 'Összes :label',
            'group' => ':group összesítése',
            'page' => 'Ezen az oldalon',
        ],
        'summarizers' => [
            'average' => [
                'label' => 'Átlag',
            ],
            'count' => [
                'label' => 'Darab',
            ],
            'sum' => [
                'label' => 'Összeg',
            ],
        ],
    ],
    'actions' => [
        'move_record_up' => [
            'label' => ':label mozgatása feljebb',
        ],
        'move_record_down' => [
            'label' => ':label mozgatása lejjebb',
        ],
        'disable_reordering' => [
            'label' => 'Átrendezés befejezése',
        ],
        'enable_reordering' => [
            'label' => 'Átrendezés',
        ],
        'filter' => [
            'label' => 'Szűrés',
        ],
        'group' => [
            'label' => 'Csoportosítás',
        ],
        'open_bulk_actions' => [
            'label' => 'Csoportos műveletek',
        ],
        'toggle_columns' => [
            'label' => 'Oszlopok láthatósága',
        ],
        'reorder_record' => [
            'label' => ':key elem átrendezése',
        ],
        'toggle_record_content' => [
            'label' => ':key elem kinyitása vagy összecsukása',
        ],
    ],
    'empty' => [
        'heading' => 'Nincs megjeleníthető elem',
        'description' => 'Hozz létre egy újat a kezdéshez.',
    ],
    'filters' => [
        'actions' => [
            'apply' => [
                'label' => 'Szűrők alkalmazása',
            ],
            'remove' => [
                'label' => 'Szűrő megszüntetése',
            ],
            'remove_all' => [
                'label' => 'Az összes szűrő megszüntetése',
                'tooltip' => 'Az összes szűrő megszüntetése',
            ],
            'reset' => [
                'label' => 'Visszaállítás',
            ],
        ],
        'heading' => 'Szűrők',
        'indicator' => 'Aktív szűrők',
        'multi_select' => [
            'placeholder' => 'Mind',
        ],
        'select' => [
            'placeholder' => 'Mind',
        ],
        'trashed' => [
            'label' => 'Törölt elemek',
            'only_trashed' => 'Csak a törölt elemek',
            'with_trashed' => 'Törölt elemekkel együtt',
            'without_trashed' => 'Törölt elemek nélkül',
        ],
    ],
    'grouping' => [
        'fields' => [
            'group' => [
                'label' => 'Csoportosítás',
                'placeholder' => 'Csoportosítás',
            ],
            'direction' => [
                'label' => 'Csoportosítás iránya',
                'options' => [
                    'asc' => 'Növekvő',
                    'desc' => 'Csökkenő',
                ],
            ],
        ],
    ],
    'reorder_indicator' => 'Kattints az elemekre és mozgasd őket az átrendezéshez.',
    'selection_indicator' => [
        'selected_count' => '1 elem kiválasztva|:count elem kiválasztva',
        'actions' => [
            'select_all' => [
                'label' => 'Mind a(z) :count elem kijelölése',
            ],
            'deselect_all' => [
                'label' => 'Kijelölés megszüntetése',
            ],
        ],
    ],
    'sorting' => [
        'fields' => [
            'column' => [
                'label' => 'Rendezés',
            ],
            'direction' => [
                'label' => 'Rendezési irány',
                'options' => [
                    'asc' => 'Növekvő',
                    'desc' => 'Csökkenő',
                ],
            ],
        ],
    ],
    'column_manager' => [
        'actions' => [
            'reorder' => [
                'label' => 'Oszlop átrendezése',
            ],
            'reset' => [
                'label' => 'Visszaállítás',
            ],
            'move_up' => [
                'label' => ':column mozgatása feljebb',
            ],
            'move_down' => [
                'label' => ':column mozgatása lejjebb',
            ],
        ],
        'position' => ':position. pozíció a(z) :count közül.',
        'position_in_group' => ':position. pozíció a(z) :count közül, :group csoportban.',
        'reordered' => ':column áthelyezve a(z) :position. pozícióba a(z) :count közül.',
        'reordered_deferred' => ':column áthelyezve a(z) :position. pozícióba a(z) :count közül. A módosítás mentéséhez alkalmazza az oszlopokat.',
        'reordered_in_group' => ':column áthelyezve a(z) :position. pozícióba a(z) :count közül, :group csoportban.',
        'reordered_in_group_deferred' => ':column áthelyezve a(z) :position. pozícióba a(z) :count közül, :group csoportban. A módosítás mentéséhez alkalmazza az oszlopokat.',
    ],
    'reordering' => [
        'position' => ':label, :position. pozíció a(z) :count közül.',
        'moved' => ':label áthelyezve a(z) :position. pozícióba a(z) :count közül.',
    ],
    'loading' => 'Betöltés...',
    'result_count' => '{0} Nincs találat|{1} :count találat|[2,*] :count találat',
];
