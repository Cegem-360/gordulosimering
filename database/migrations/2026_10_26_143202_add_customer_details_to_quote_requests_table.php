<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Az ajánlatkérő is ugyanazokat az adatokat adja meg, mint a megrendelő:
 * magánszemély vagy cég, cégnél adószám, és a számlázási cím (az ügyfél
 * kérése, 2026-10-26). A korábbi ajánlatkéréseknél üresek maradnak.
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table): void {
            $table->string('customer_type')->nullable()->after('user_id');
            $table->string('vat_number')->nullable()->after('company');
            $table->string('postcode')->nullable()->after('vat_number');
            $table->string('city')->nullable()->after('postcode');
            $table->string('address_1')->nullable()->after('city');
            $table->string('address_2')->nullable()->after('address_1');
            $table->string('country')->nullable()->after('address_2');
            $table->string('state')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table): void {
            $table->dropColumn(['customer_type', 'vat_number', 'postcode', 'city', 'address_1', 'address_2', 'country', 'state']);
        });
    }
};
