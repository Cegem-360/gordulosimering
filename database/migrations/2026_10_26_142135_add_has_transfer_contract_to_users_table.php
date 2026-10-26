<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Banki átutalással csak az átutalásos szerződéssel rendelkező partnerek
 * fizethetnek; a szerződést az adminban a partner adatlapján pipálják be
 * (az ügyfél kérése, 2026-10-26).
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('has_transfer_contract')->default(false)->after('base_discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('has_transfer_contract');
        });
    }
};
