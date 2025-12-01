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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic product information
            $table->string('group_code', 50)->nullable();
            $table->string('product_code')->nullable();
            $table->boolean('is_service')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->unique()->index();
            $table->string('catalog_number')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('size', 100)->nullable();
            $table->decimal('weight', 10, 3)->nullable();

            // Quality and classification
            $table->string('rating', 100)->nullable();
            $table->string('quality', 100)->nullable();
            $table->string('product_variety')->nullable();
            $table->string('trade_type', 50)->nullable();
            $table->string('usage_type', 50)->nullable();

            // Currency and pricing
            $table->string('currency_settlement', 50)->nullable();
            $table->string('discount_group', 50)->nullable();
            $table->boolean('is_on_sale')->nullable();
            $table->decimal('sale_percentage', 5, 2)->nullable();
            $table->string('pricing', 10)->nullable();
            $table->decimal('list_price', 12, 2)->nullable();
            $table->decimal('list_discount', 5, 2)->nullable();
            $table->decimal('purchase_currency_price', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->decimal('currency_multiplier', 10, 4)->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('profit_margin', 5, 2)->nullable();
            $table->decimal('net_selling_price', 12, 2)->nullable();
            $table->string('vat_class', 20)->nullable();
            $table->decimal('gross_selling_price', 12, 2)->nullable();

            // Stock and units
            $table->string('quantity_unit', 20)->nullable();
            $table->string('secondary_unit', 20)->nullable();
            $table->integer('minimum_stock')->nullable();
            $table->integer('maximum_stock')->nullable();
            $table->integer('buffer_stock')->nullable();
            $table->integer('order_unit')->nullable();

            // Official codes
            $table->string('ksh_prefix', 20)->nullable();
            $table->string('ksh_number', 20)->nullable();

            // Supplier and notes
            $table->string('supplier')->nullable();
            $table->string('short_note')->nullable();
            $table->text('description')->nullable();

            // Barcodes
            $table->string('barcode')->nullable();
            $table->string('ean_code', 50)->nullable();

            // Order quantities
            $table->integer('min_order_quantity')->nullable();
            $table->integer('trade_quantity')->nullable();
            $table->integer('pallet_quantity')->nullable();

            // Custom fields
            $table->json('custom_fields')->nullable();

            // Images data
            $table->json('images')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
