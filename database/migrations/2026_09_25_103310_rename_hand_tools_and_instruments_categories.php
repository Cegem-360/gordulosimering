<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Az ügyfél kérésére a "KÉZI SZERSZÁMOK, MŰSZEREK" főkategória neve
 * "KÉZISZERSZÁMOK ÉS MŰSZEREK", alatta a "MŰSZEREK" pedig "MÉRŐ- ÉS
 * ELLENŐRZŐ MŰSZEREK". A SZÍJHATÁS alatti "MÉRŐ ÉS ELLENÖRZŐ MŰSZER" helyesen
 * "MÉRŐ- ÉS ELLENŐRZŐ MŰSZER" (a slug nem változik). A web_kategoriak.tsv is az új neveket tartalmazza; a
 * kategória-import név szerint keresi a meglévő sort, ezért azt is át kell
 * nevezni, különben párhuzamos fa jönne létre.
 */
return new class() extends Migration
{
    private const string OLD_ROOT = 'KÉZI SZERSZÁMOK, MŰSZEREK';

    private const string NEW_ROOT = 'KÉZISZERSZÁMOK ÉS MŰSZEREK';

    private const string OLD_CHILD = 'MŰSZEREK';

    private const string NEW_CHILD = 'MÉRŐ- ÉS ELLENŐRZŐ MŰSZEREK';

    private const string BELT_DRIVE = 'SZÍJHATÁS';

    private const string OLD_BELT_INSTRUMENT = 'MÉRŐ ÉS ELLENÖRZŐ MŰSZER';

    private const string NEW_BELT_INSTRUMENT = 'MÉRŐ- ÉS ELLENŐRZŐ MŰSZER';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->rename(
            [self::OLD_ROOT, 'kezi-szerszamok-muszerek'],
            [self::NEW_ROOT, 'keziszerszamok-es-muszerek'],
            [self::OLD_CHILD, 'kezi-szerszamok-muszerek-muszerek'],
            [self::NEW_CHILD, 'keziszerszamok-es-muszerek-mero-es-ellenorzo-muszerek'],
        );
        $this->renameBeltInstrument(self::OLD_BELT_INSTRUMENT, self::NEW_BELT_INSTRUMENT);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->rename(
            [self::NEW_ROOT, 'keziszerszamok-es-muszerek'],
            [self::OLD_ROOT, 'kezi-szerszamok-muszerek'],
            [self::NEW_CHILD, 'keziszerszamok-es-muszerek-mero-es-ellenorzo-muszerek'],
            [self::OLD_CHILD, 'kezi-szerszamok-muszerek-muszerek'],
        );
        $this->renameBeltInstrument(self::NEW_BELT_INSTRUMENT, self::OLD_BELT_INSTRUMENT);
    }

    private function renameBeltInstrument(string $from, string $to): void
    {
        $beltDriveIds = DB::table('product_categories')->where('name', self::BELT_DRIVE)->pluck('id');

        DB::table('product_categories')
            ->whereIn('category_id', $beltDriveIds)
            ->where('name', $from)
            ->update(['name' => $to, 'updated_at' => now()]);
    }

    /**
     * @param  array{0: string, 1: string}  $fromRoot
     * @param  array{0: string, 1: string}  $toRoot
     * @param  array{0: string, 1: string}  $fromChild
     * @param  array{0: string, 1: string}  $toChild
     */
    private function rename(array $fromRoot, array $toRoot, array $fromChild, array $toChild): void
    {
        $rootId = DB::table('product_categories')->whereNull('category_id')->where('name', $fromRoot[0])->value('id');

        if ($rootId === null) {
            return;
        }

        $this->renameRow($rootId, $toRoot);

        $childId = DB::table('product_categories')->where('category_id', $rootId)->where('name', $fromChild[0])->value('id');

        if ($childId !== null) {
            $this->renameRow($childId, $toChild);
        }
    }

    /**
     * @param  array{0: string, 1: string}  $to
     */
    private function renameRow(int $id, array $to): void
    {
        [$name, $slug] = $to;
        $slugTaken = DB::table('product_categories')->where('slug', $slug)->where('id', '!=', $id)->exists();

        DB::table('product_categories')->where('id', $id)->update([
            'name' => $name,
            ...($slugTaken ? [] : ['slug' => $slug]),
            'updated_at' => now(),
        ]);
    }
};
