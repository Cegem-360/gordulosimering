<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Négy nagybetűs levél a web_kategoriak.tsv-ben valójában egyetlen termék
 * neve, nem kategória (az ügyfél jelezte a "SKF VASÚTI ÁGYTOKCSAPÁGY"-nál). A
 * TSV-ben már kisbetűs termék-sorok, így az import a szülőjükhöz rendeli a
 * terméket; ez a meglévő kategóriasort törli, a termékeit pedig a szülőre
 * teszi. Az import nem töröl kategóriát, ezért kell a migráció.
 */
return new class() extends Migration
{
    /**
     * Szülő neve => a kategóriává vált terméknév.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const array PRODUCT_NAMED_CATEGORIES = [
        ['CSAPÁGYAK', 'SKF VASÚTI ÁGYTOKCSAPÁGY'],
        ['SZERELÉSTECHNIKAI ESZKÖZÖK, ALKATRÉSZEK', 'SKF/LINCOLN 084110'],
        ['ZSÍR, OLAJ', 'GRAFITOS ZSÍR'],
        ['FURATBA', 'SEEGER DIN9926'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::PRODUCT_NAMED_CATEGORIES as [$parentName, $name]) {
            $categories = DB::table('product_categories as category')
                ->join('product_categories as parent', 'parent.id', '=', 'category.category_id')
                ->where('parent.name', $parentName)
                ->where('category.name', $name)
                ->whereNotExists(fn ($query) => $query->from('product_categories as child')
                    ->whereColumn('child.category_id', 'category.id'))
                ->get(['category.id', 'category.category_id']);

            foreach ($categories as $category) {
                $this->moveProductsToParent($category->id, $category->category_id);
                DB::table('product_categories')->where('id', $category->id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Nincs mit visszaállítani: a régi TSV-vel futó import újra létrehozná a
     * kategóriákat.
     */
    public function down(): void {}

    private function moveProductsToParent(int $categoryId, int $parentId): void
    {
        $productIds = DB::table('category_product')->where('category_id', $categoryId)->pluck('product_id');
        $alreadyInParent = DB::table('category_product')->where('category_id', $parentId)
            ->whereIn('product_id', $productIds)->pluck('product_id');

        DB::table('category_product')->insert(
            $productIds->diff($alreadyInParent)
                ->map(fn (int $productId): array => ['category_id' => $parentId, 'product_id' => $productId])
                ->values()
                ->all(),
        );

        DB::table('category_product')->where('category_id', $categoryId)->delete();
    }
};
