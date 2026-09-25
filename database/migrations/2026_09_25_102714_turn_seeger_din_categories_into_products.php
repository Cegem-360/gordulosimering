<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Az előző migráció folytatása: a SEEGER GYŰRŰ alatt a "SEEGER DIN6799",
 * "SEEGER DIN9927" és "SEEGER DIN9928" is terméknév (minden terméküket így
 * hívják), nem kategória. A TSV-ben már kisbetűs termék-sorok; ez törli a
 * meglévő kategóriasorokat, a termékeiket pedig a szülőre teszi.
 */
return new class() extends Migration
{
    /**
     * Szülő neve => a kategóriává vált terméknév.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const array PRODUCT_NAMED_CATEGORIES = [
        ['TENGELYRE', 'SEEGER DIN6799'],
        ['TENGELYRE', 'SEEGER DIN9927'],
        ['FURATBA', 'SEEGER DIN9928'],
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
