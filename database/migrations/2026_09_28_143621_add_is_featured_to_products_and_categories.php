<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A főoldal "Kiemelt kategóriáink" és "Kiemelt termékeink" szekcióját az
 * ügyfél az adminban jelöli ki. Az ERP-szinkron csak az exportból kapott
 * mezőket írja, ezt a jelölést nem írja felül.
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['products', 'product_categories'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->boolean('is_featured')->default(false)->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['products', 'product_categories'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropIndex(['is_featured']);
                $table->dropColumn('is_featured');
            });
        }
    }
};
