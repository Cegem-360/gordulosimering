<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Attribútum címkék
|--------------------------------------------------------------------------
|
| Modell attribútumok magyar címkéi. A Filament táblázatoszlopok, űrlapmezők,
| infolist bejegyzések, szűrők és importoszlopok innen veszik a címkéjüket,
| amíg a komponens nem kap saját `->label()` hívást. Ismeretlen attribútum
| esetén a Filament a névből generált címkét használja.
|
*/

return [
    'name' => 'Név',
    'slug' => 'URL-kulcs',
    'title' => 'Cím',
    'description' => 'Leírás',
    'created_at' => 'Létrehozva',
    'updated_at' => 'Módosítva',

    'group_code' => 'Csoportkód',
    'product_code' => 'Termékkód',
    'is_service' => 'Szolgáltatás',
    'is_web_visible' => 'Webshopban látszik',
    'is_inactive' => 'Inaktív',
    'catalog_number' => 'Katalógusszám',
    'type' => 'Típus',
    'size' => 'Méret',
    'weight' => 'Súly',
    'rating' => 'Minősítés',
    'quality' => 'Minőség',
    'product_variety' => 'Termékféleség',
    'trade_type' => 'Ker. típus',
    'usage_type' => 'Felh. típus',
    'currency_settlement' => 'Deviza elsz.',
    'discount_group' => 'Kedvezmény csoport',
    'is_on_sale' => 'Akciós',
    'sale_percentage' => 'Akció %',
    'pricing' => 'Árképzés',
    'net_selling_price' => 'Nettó eladási ár',
    'vat_class' => 'ÁFA osztály',
    'gross_selling_price' => 'Bruttó eladási ár',
    'quantity_unit' => 'Mennyiségi egység',
    'secondary_unit' => 'Másodlagos egység',
    'minimum_stock' => 'Minimum készlet',
    'maximum_stock' => 'Maximum készlet',
    'buffer_stock' => 'Puffer készlet',
    'order_unit' => 'Rendelési egység',
    'ksh_prefix' => 'KSH előtag',
    'ksh_number' => 'KSZ szám',
    'supplier' => 'Beszállító',
    'short_note' => 'Rövid megjegyzés',
    'barcode' => 'Vonalkód',
    'ean_code' => 'EAN kód',
    'min_order_quantity' => 'Min. rendelhető',
    'trade_quantity' => 'Ker. mennyiség',
    'pallet_quantity' => 'Raklap mennyiség',
    'custom_fields' => 'Egyéni mezők',
    'featured_image' => 'Kiemelt kép',
    'images' => 'További képek',
    'documents' => 'Dokumentumok',
    'categories' => 'Kategóriák',
    'categories.name' => 'Kategóriák',

    'category_id' => 'Szülő kategória',
    'display' => 'Megjelenítés',
    'parentCategory.name' => 'Szülő kategória',

    'user_id' => 'Vevő',
    'user.name' => 'Vevő',
    'shipping_method_id' => 'Szállítási mód',
    'shippingMethod.name' => 'Szállítási mód',
    'payment_method' => 'Fizetési mód',
    'payment_method_title' => 'Fizetési mód megnevezése',
    'set_paid' => 'Fizetve',
    'order_key' => 'Rendelés azonosító',
    'order_status' => 'Rendelés állapota',
    'order_currency' => 'Pénznem',
    'shipping_cost' => 'Szállítási költség',
    'shipping_tracking_number' => 'Csomagkövetési szám',

    'billing_name' => 'Számlázási név',
    'billing_company_name' => 'Cégnév',
    'billing_company_office' => 'Cég telephelye',
    'billing_vat_number' => 'Adószám',
    'billing_email' => 'Számlázási e-mail',
    'billing_phone' => 'Számlázási telefon',
    'billing_postcode' => 'Számlázási postakód',
    'billing_city' => 'Számlázási város',
    'billing_address_1' => 'Számlázási cím',
    'billing_address_2' => 'Számlázási cím 2.',
    'billing_country' => 'Számlázási ország',
    'billing_state' => 'Számlázási megye',

    'shipping_name' => 'Szállítási név',
    'shipping_postcode' => 'Szállítási postakód',
    'shipping_city' => 'Szállítási város',
    'shipping_address_1' => 'Szállítási cím',
    'shipping_address_2' => 'Szállítási cím 2.',
    'shipping_country' => 'Szállítási ország',
    'shipping_state' => 'Szállítási megye',

    'product_id' => 'Termék',
    'product.name' => 'Termék',
    'product.product_code' => 'Cikkszám',
    'quantity' => 'Mennyiség',
    'tax_class' => 'Adóosztály',

    'email' => 'E-mail cím',
    'email_verified_at' => 'E-mail megerősítve',
    'password' => 'Jelszó',
    'phone' => 'Telefon',

    'cost' => 'Szállítási költség',

    'excerpt' => 'Kivonat',
    'content' => 'Tartalom',
    'is_published' => 'Közzétéve',
    'is_featured' => 'Kiemelt',
    'published_at' => 'Közzététel dátuma',
    'sort_order' => 'Sorrend',
    'og_type' => 'OG típus',
    'schema_markup' => 'Egyéni JSON-LD (schema.org)',

    'seo' => [
        'title' => 'SEO cím',
        'description' => 'Meta leírás',
        'keywords' => 'Kulcsszavak',
        'canonical_url' => 'Kanonikus URL',
        'no_index' => 'Kizárás a keresőkből (noindex)',
        'no_follow' => 'Linkek nem követése (nofollow)',
    ],
];
