<?php

declare(strict_types=1);

return [
    'admin_email' => env('SHOP_ADMIN_EMAIL', 'admin@example.com'),

    /*
     * A Kapcsolat oldal űrlapjának címzettje: az ügyfél központi címe.
     */
    'contact_email' => env('SHOP_CONTACT_EMAIL', 'gs@gordulo-simmering.hu'),

    /*
     * Az ügyfél által karbantartott, TSV-ként publikált termékkép-táblázat
     * (TERMOKKOD, TERMEKNEV, KEP 1, KEP 2). Az app:import-product-images
     * --sheet innen tölti le a legfrissebb változatot.
     */
    'product_images_sheet_url' => env('PRODUCT_IMAGES_SHEET_URL', 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRxWp7IGBpQ-BxX3Wxh_CQegCVErrSxeZTyqAlj8kZ_zI3G5QfM_RpwqnbwVRSG7KhIOpy1xFLbmeHs/pub?gid=1566447663&single=true&output=tsv'),
];
