<?php

declare(strict_types=1);

return [
    'direction' => 'ltr',
    'actions' => [
        'billing' => [
            'label' => 'Előfizetések kezelése',
        ],
        'logout' => [
            'label' => 'Kijelentkezés',
        ],
        'open_database_notifications' => [
            'label' => 'Értesítések',
            'label_with_unread_count' => '{1} Értesítések, :count olvasatlan értesítés|[2,*] Értesítések, :count olvasatlan értesítés',
        ],
        'open_user_menu' => [
            'label' => 'Felhasználói menü',
        ],
        'sidebar' => [
            'collapse' => [
                'label' => 'Oldalsáv elrejtése',
            ],
            'expand' => [
                'label' => 'Oldalsáv megjelenítése',
            ],
        ],
        'theme_switcher' => [
            'dark' => [
                'label' => 'Sötét mód bekapcsolása',
            ],
            'light' => [
                'label' => 'Világos mód bekapcsolása',
            ],
            'system' => [
                'label' => 'Rendszertéma követése',
            ],
            'label' => 'Téma',
        ],
    ],
    'avatar' => [
        'alt' => ':name avatárja',
    ],
    'logo' => [
        'alt' => ':name logója',
    ],
    'skip_to_content' => [
        'label' => 'Ugrás a tartalomra',
    ],
    'navigation' => [
        'label' => 'Oldalsáv navigáció',
    ],
    'topbar' => [
        'label' => 'Felső sáv',
    ],
    'tenant_menu' => [
        'search_field' => [
            'label' => 'Bérlő keresése',
            'placeholder' => 'Keresés',
        ],
    ],
];
