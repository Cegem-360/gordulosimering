<?php

declare(strict_types=1);

return [
    'modal' => [
        'form' => [
            'code' => [
                'actions' => [
                    'resend' => [
                        'notifications' => [
                            'throttled' => [
                                'title' => 'Túl sok újraküldés. Várj, mielőtt új kódot kérnél.',
                            ],
                        ],
                    ],
                ],
                'messages' => [
                    'rate_limited' => 'Túl sok próbálkozás. Kérlek, próbáld újra később.',
                ],
            ],
        ],
    ],
];
