<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    private const float VAT_RATE = 0.27;

    /**
     * The list price and the discount are kept with the item from now on; the
     * older items have no record of them. Their VAT, saved as 0 so far, is
     * filled in at 27%.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('regular_price', 12, 2)->nullable()->after('total_tax');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('regular_price');
        });

        DB::table('order_items')->orderBy('id')->chunkById(500, function ($items): void {
            foreach ($items as $item) {
                if ((float) $item->total_tax > 0 || (float) $item->subtotal_tax > 0) {
                    continue;
                }

                DB::table('order_items')->where('id', $item->id)->update([
                    'total_tax' => number_format((float) $item->total * self::VAT_RATE, 2, '.', ''),
                    'subtotal_tax' => number_format((float) $item->subtotal * self::VAT_RATE, 2, '.', ''),
                    'tax_class' => '27%',
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn(['regular_price', 'discount_percentage']);
        });
    }
};
