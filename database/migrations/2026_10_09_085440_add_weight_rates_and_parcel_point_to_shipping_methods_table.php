<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * A GLS nettó díjai súlysávonként, átutalásos és utánvétes rendelésre.
     *
     * @var array<int, array{max_weight: int, bank_transfer: int, cash_on_delivery: int}>
     */
    private const array GLS_RATES = [
        ['max_weight' => 3, 'bank_transfer' => 2550, 'cash_on_delivery' => 3550],
        ['max_weight' => 15, 'bank_transfer' => 2950, 'cash_on_delivery' => 3950],
        ['max_weight' => 30, 'bank_transfer' => 3750, 'cash_on_delivery' => 4750],
    ];

    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->json('rates')->nullable()->after('cost');
            $table->boolean('requires_parcel_point')->default(false)->after('rates');
        });

        DB::table('shipping_methods')
            ->where('name', 'gls')
            ->update(['rates' => json_encode(self::GLS_RATES)]);

        if (DB::table('shipping_methods')->where('name', 'gls-parcel-point')->doesntExist()) {
            DB::table('shipping_methods')->insert([
                'name' => 'gls-parcel-point',
                'title' => 'GLS csomagpont / csomagautomata',
                'slug' => 'gls-csomagpont',
                'description' => 'Átvétel a kiválasztott GLS csomagponton vagy GLS csomagautomatában, általában 1-2 munkanapon belül.',
                'cost' => 0,
                'rates' => json_encode(self::GLS_RATES),
                'requires_parcel_point' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('shipping_methods')->where('name', 'gls-parcel-point')->delete();

        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->dropColumn(['rates', 'requires_parcel_point']);
        });
    }
};
