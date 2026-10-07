<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Az "Egyéb adatok és kódok" mezőinek láthatósága a termékoldalon,
 * termékenként (mezőnév => bool). Ami nincs benne, az látszik. Az
 * ERP-szinkron nem írja.
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('field_visibility')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('field_visibility');
        });
    }
};
