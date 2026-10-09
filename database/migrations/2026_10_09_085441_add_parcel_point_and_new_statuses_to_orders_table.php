<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    private const array STATUSES = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'trash'];

    private const array NEW_STATUSES = ['shipped', 'ready-for-pickup'];

    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->enum('order_status', [...self::STATUSES, ...self::NEW_STATUSES])->default('pending')->change();
            $table->string('parcel_point_id')->nullable()->after('shipping_tracking_number');
            $table->string('parcel_point_name')->nullable()->after('parcel_point_id');
            $table->string('parcel_point_address')->nullable()->after('parcel_point_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['parcel_point_id', 'parcel_point_name', 'parcel_point_address']);
            $table->enum('order_status', self::STATUSES)->default('pending')->change();
        });
    }
};
