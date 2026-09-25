<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A VEGYI ÁRUK alkategóriáinak sorrendje az ügyfél kérésére. A
 * web_kategoriak.tsv is ebben a sorrendben tartalmazza őket, de az import
 * csak új kategóriának ad sorrendet, ezért a meglévőket ez számozza át. A
 * kategóriák a saját, már kiosztott sort_order értékeiket kapják meg az új
 * sorrendben, így a VEGYI ÁRUK helye a többi kategória között nem változik.
 */
return new class() extends Migration
{
    private const string PARENT = 'VEGYI ÁRUK';

    /**
     * @var array<int, string>
     */
    private const array ORDER = [
        'PILLANATRAGASZTÓ',
        'CSAVARRÖGZÍTŐ',
        'CSAPÁGYRÖGZÍTŐ',
        'MENETTÖMÍTŐ',
        'FELÜLETTÖMÍTŐ',
        'KÉTKOMPONENSŰ RAGASZTÓ',
        'BERÁGÓDÁSGÁTLÓ',
        'TISZTÍTÁS, ZSÍRTALANÍTÁS',
        'AKTIVÁTOR, PRIMER',
        'ZSÍR, OLAJ',
        'UV FÉNYRE KÖTŐ RAGASZTÓ',
        'EGYÉB RAGASZTÓ ÉS TÖMÍTŐ',
        'RAGASZTÓSZALAG',
        'KARBANTARTÁSI TERMÉKEK',
        'ADAGOLÓ ESZKÖZ',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parentId = DB::table('product_categories')->whereNull('category_id')->where('name', self::PARENT)->value('id');

        if ($parentId === null) {
            return;
        }

        $children = DB::table('product_categories')
            ->where('category_id', $parentId)
            ->whereIn('name', self::ORDER)
            ->get(['id', 'name', 'sort_order'])
            ->keyBy('name');

        $slots = $children->pluck('sort_order')->filter(fn (?int $order): bool => $order !== null)->sort()->values();

        if ($slots->count() !== $children->count()) {
            $slots = collect(range(1, $children->count()));
        }

        $position = 0;

        foreach (self::ORDER as $name) {
            if (! $children->has($name)) {
                continue;
            }

            DB::table('product_categories')->where('id', $children[$name]->id)->update([
                'sort_order' => $slots[$position++],
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * A régi sorrend a kategória-táblázat korábbi változatából állna vissza;
     * ezt nem érdemes visszaírni.
     */
    public function down(): void {}
};
