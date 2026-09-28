<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Az admin felületre csak az adminok léphetnek be. Az élesítéskor meglévő két
 * fiók a cégé, ezek lesznek az adminok; a regisztráló vevők nem.
 */
return new class() extends Migration
{
    /** @var array<int, string> */
    private const array ADMIN_EMAILS = ['admin@admin.com', 'gs@gordulo-simmering.hu'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        DB::table('users')->whereIn('email', self::ADMIN_EMAILS)->update(['is_admin' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_admin');
        });
    }
};
