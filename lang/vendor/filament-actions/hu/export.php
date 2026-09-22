<?php

declare(strict_types=1);

return [
    'label' => ':label exportálása',
    'modal' => [
        'heading' => ':label exportálása',
        'form' => [
            'columns' => [
                'label' => 'Oszlopok',
                'form' => [
                    'is_enabled' => [
                        'label' => ':column bekapcsolva',
                    ],
                    'label' => [
                        'label' => ':column fejléc',
                    ],
                ],
                'actions' => [
                    'select_all' => [
                        'label' => 'Összes kijelölése',
                    ],
                    'deselect_all' => [
                        'label' => 'Kijelölés megszüntetése',
                    ],
                ],
            ],
        ],
        'actions' => [
            'export' => [
                'label' => 'Exportálás',
            ],
        ],
    ],
    'notifications' => [
        'completed' => [
            'title' => 'Az exportálás befejeződött',
            'actions' => [
                'download_csv' => [
                    'label' => 'CSV letöltése',
                ],
                'download_xlsx' => [
                    'label' => 'XLSX letöltése',
                ],
            ],
        ],
        'max_rows' => [
            'title' => 'Túl sok exportálandó sor',
            'body' => 'Nem exportálhatsz több mint 1 sort egyszerre.|Nem exportálhatsz több mint :count sor egyszerre.',
        ],
        'started' => [
            'title' => 'Az exportálás elkezdődött',
            'body' => 'Elkezdődött 1 sor exportálása a háttérben. Ha elkészült, akkor a letöltési hivatkozás egy új értesítésben fog megjelenni.|Elkezdődött :count sor exportálása a háttérben. Ha elkészült, akkor a letöltési hivatkozás egy új értesítésben fog megjelenni.',
        ],
        'no_columns' => [
            'title' => 'Nincs kijelölt oszlop',
            'body' => 'Legalább egy oszlopot ki kell jelölnöd az exporthoz.',
        ],
    ],
    'file_name' => 'exportálás-:export_id-:model',
];
