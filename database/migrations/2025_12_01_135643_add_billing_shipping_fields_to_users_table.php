<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Contact info
            $table->string('phone')->nullable();

            // Billing info
            $table->string('billing_name')->nullable();
            $table->string('billing_company_name')->nullable();
            $table->string('billing_vat_number')->nullable();
            $table->string('billing_company_office')->nullable();
            $table->string('billing_postcode')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_address_1')->nullable();
            $table->string('billing_address_2')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_state')->nullable();

            // Shipping info
            $table->string('shipping_name')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_address_1')->nullable();
            $table->string('shipping_address_2')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_state')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'billing_name',
                'billing_company_name',
                'billing_vat_number',
                'billing_company_office',
                'billing_postcode',
                'billing_city',
                'billing_address_1',
                'billing_address_2',
                'billing_country',
                'billing_state',
                'shipping_name',
                'shipping_postcode',
                'shipping_city',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_country',
                'shipping_state',
            ]);
        });
    }
};
