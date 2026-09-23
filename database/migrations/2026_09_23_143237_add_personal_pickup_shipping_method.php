<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Személyes átvétel a X. kerületi üzletben. Adatmigráció, mert a szállítási
 * módok adatbázis-sorok, és a már feltöltött környezetekbe is el kell jutnia;
 * a seeder csak friss telepítéskor fut.
 */
return new class() extends Migration
{
    private const string NAME = 'pickup';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('shipping_methods')->where('name', self::NAME)->exists()) {
            return;
        }

        DB::table('shipping_methods')->insert([
            'name' => self::NAME,
            'title' => 'Személyes átvétel',
            'slug' => 'szemelyes-atvetel',
            'description' => 'Átvétel X. kerületi üzletünkben (1102 Budapest, Kőrösi Csoma S. út 18-20.), miután értesítettük, hogy a rendelése átvehető.',
            'cost' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('shipping_methods')->where('name', self::NAME)->delete();
    }
};
