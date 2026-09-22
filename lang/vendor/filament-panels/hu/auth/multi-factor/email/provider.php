<?php

declare(strict_types=1);

return [
    'login_form' => [
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
        ],
    ],
];
