<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A "CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)" főkategória átnevezése "CSAPÁGYHÁZAK"-ra
 * az ügyfél kérésére. A web_kategoriak.tsv is az új nevet tartalmazza. A
 * kategória-import név szerint keresi a meglévő kategóriát, ezért csak a TSV
 * átírása egy második, párhuzamos fát hozna létre: a meglévő sort is át kell
 * nevezni.
 */
return new class() extends Migration
{
    private const string OLD_NAME = 'CSAPÁGYHÁZAK (S1,S2,S3,S4,S5)';

    private const string OLD_SLUG = 'csapagyhazak-s1s2s3s4s5';

    private const string NEW_NAME = 'CSAPÁGYHÁZAK';

    private const string NEW_SLUG = 'csapagyhazak';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->rename(self::OLD_NAME, self::NEW_NAME, self::NEW_SLUG);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->rename(self::NEW_NAME, self::OLD_NAME, self::OLD_SLUG);
    }

    private function rename(string $from, string $to, string $slug): void
    {
        $slugTaken = DB::table('product_categories')->where('slug', $slug)->exists();

        DB::table('product_categories')
            ->whereNull('category_id')
            ->where('name', $from)
            ->update([
                'name' => $to,
                ...($slugTaken ? [] : ['slug' => $slug]),
                'updated_at' => now(),
            ]);
    }
};
