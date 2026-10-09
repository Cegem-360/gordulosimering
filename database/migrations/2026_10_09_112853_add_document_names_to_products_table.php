<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A termékhez feltöltött dokumentumok eredeti fájlnevei (tárolt útvonal =>
 * eredeti név). A Filament véletlen néven menti a fájlt; a termékoldal ezt a
 * nevet mutatja a linken.
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('document_names')->nullable()->after('documents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('document_names');
        });
    }
};
