<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * The courier gives the tracking number, so an order may have none. It
     * was a required column defaulting to the string "null"; that and the
     * empty value the checkout saved become NULL.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_tracking_number')->nullable()->default(null)->change();
        });

        DB::table('orders')
            ->whereIn('shipping_tracking_number', ['null', ''])
            ->update(['shipping_tracking_number' => null]);
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereNull('shipping_tracking_number')
            ->update(['shipping_tracking_number' => 'null']);

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_tracking_number')->default('null')->change();
        });
    }
};
