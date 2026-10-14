<?php

declare(strict_types=1);

use App\Models\RecommendationRule;
use App\Services\RecommendationRuleImporter;
use Illuminate\Database\Migrations\Migration;

/*
 * The client's "gyakran veszik együtt" list (gyakran-veszik-egyutt.xlsx,
 * 2026-10-14), read with App\Services\XlsxReader: bought (A–C) → offered
 * (D–F). Rows whose categories are missing (e.g. on an empty test database)
 * are skipped; the admin's Ajánlások page can import the sheet again.
 */
return new class() extends Migration
{
    public function up(): void
    {
        resolve(RecommendationRuleImporter::class)->importRows([
            ['MINDENHEZ', '', '', 'VEGYI ÁRUK', 'TISZTÍTÁS, ZSÍRTALANÍTÁS', ''],
            ['MINDENHEZ', '', '', 'MUNKAVÉDELMI CIPŐ, KESZTYŰ', 'MUNKAVÉDELMI KESZTYŰ', ''],
            ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', ''],
            ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'ZSÍRZÓGOMB', ''],
            ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'HÉZAGOLÓ ALÁTÉT', ''],
            ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'KOMPENZÁCIÓS LEMEZ', ''],
            ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'VEGYI ÁRUK', 'CSAPÁGYRÖGZÍTŐ', ''],
            ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYAK', 'GOLYÓS CSAPÁGY', '', 'KÉZISZERSZÁMOK ÉS MŰSZEREK', 'CSAPÁGYSZERELŐ SZERSZÁM', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'ZSÍRZÓGOMB', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'HÉZAGOLÓ ALÁTÉT', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'KOMPENZÁCIÓS LEMEZ', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'TÖMÍTÉSEK', 'SZIMERING', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'VEGYI ÁRUK', 'CSAPÁGYRÖGZÍTŐ', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYAK', 'GÖRGŐS CSAPÁGY', '', 'KÉZISZERSZÁMOK ÉS MŰSZEREK', 'CSAPÁGYSZERELŐ SZERSZÁM', ''],
            ['CSAPÁGYAK', 'VEZETŐ- ÉS TÁMASZTÓ GÖRGŐ', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', ''],
            ['CSAPÁGYAK', 'VEZETŐ- ÉS TÁMASZTÓ GÖRGŐ', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'ZSÍRZÓGOMB', ''],
            ['CSAPÁGYAK', 'SIKLÓ CSAPÁGY / CSÚSZÓ CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'ZSÍRZÓGOMB', ''],
            ['CSAPÁGYAK', 'GÖMBCSUKLÓ', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', ''],
            ['CSAPÁGYAK', 'GÖMBCSUKLÓ', '', 'VEGYI ÁRUK', 'CSAPÁGYRÖGZÍTŐ', ''],
            ['CSAPÁGYAK', 'GÖMBCSUKLÓ', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYAK', 'LINEÁRIS CSAPÁGY', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYAK', 'SZABADONFUTÓ CSAPÁGY', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'HÉZAGOLÓ ALÁTÉT', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'KOMPENZÁCIÓS LEMEZ', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'TÖMÍTÉSEK', 'SZIMERING', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'VEGYI ÁRUK', 'CSAPÁGYRÖGZÍTŐ', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'VEGYI ÁRUK', 'FELÜLETTÖMÍTŐ', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYAK', 'JÁRMŰIPARI CSAPÁGY', '', 'KÉZISZERSZÁMOK ÉS MŰSZEREK', 'CSAPÁGYSZERELŐ SZERSZÁM', ''],
            ['CSAPÁGYHÁZAK', 'CSAPÁGYHÁZ Y CSAPÁGYAKHOZ', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'ZSÍRZÓGOMB', ''],
            ['CSAPÁGYHÁZAK', 'CSAPÁGYHÁZ BEÁLLÓ CSAPÁGYAKHOZ', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'ZSÍRZÓGOMB', ''],
            ['CSAPÁGYHÁZAK', 'CSAPÁGYHÁZ BEÁLLÓ CSAPÁGYAKHOZ', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'GOLYÓ, TŰGÖRGŐ', '', 'VEGYI ÁRUK', 'ZSÍR, OLAJ', ''],
            ['CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', '', 'CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SEEGER GYŰRŰ', 'SEEGER FOGÓ'],
            ['CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'CSAPÁGYANYA ÉS BIZTOSÍTÓLEMEZ', '', 'HNA', '', ''],
            ['CSAPÁGYTARTOZÉKOK, ALKATRÉSZEK', 'SZORÍTÓ- ÉS LEHÚZÓHÜVELY', '', 'HNA', '', ''],
            ['CSAPÁGYEGYSÉGEK', '', '', 'TMAS', '', ''],
            ['CSAPÁGYHÁZAK', '', '', 'TMAS', '', ''],
        ]);
    }

    public function down(): void
    {
        RecommendationRule::query()->delete();
    }
};
