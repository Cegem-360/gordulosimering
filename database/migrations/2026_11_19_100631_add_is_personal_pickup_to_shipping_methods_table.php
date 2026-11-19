<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A személyes átvételt eddig a „pickup” belső név jelölte, de az adminban
 * átírható, élesen „Személyes átvétel” lett belőle, így az üzleti fizetési
 * módok nem voltak választhatók. Mostantól külön kapcsoló jelöli.
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->boolean('is_personal_pickup')->default(false)->after('requires_parcel_point');
        });

        DB::table('shipping_methods')
            ->where(fn ($query) => $query->whereIn('name', ['pickup', 'Személyes átvétel'])->orWhere('slug', 'szemelyes-atvetel'))
            ->update(['is_personal_pickup' => true]);
    }

    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table): void {
            $table->dropColumn('is_personal_pickup');
        });
    }
};
