<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A termékek ERP-s „Csoportkód”-jai (CT, FA, SM…), amelyekre a vevőknek egyedi
 * kedvezményt lehet adni. A meglévő termékek kódjaiból töltjük fel.
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discount_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('products')
            ->whereNotNull('group_code')
            ->where('group_code', '!=', '')
            ->distinct()
            ->orderBy('group_code')
            ->pluck('group_code')
            ->each(fn (string $code): bool => DB::table('discount_groups')->insert([
                'code' => $code,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_groups');
    }
};
