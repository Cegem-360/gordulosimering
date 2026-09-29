<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A szűrősáv attribútumai, a névből és a méretből számolva
 * (ProductAttributeExtractor); az app:extract-product-attributes tölti fel.
 */
return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('inner_diameter', 10, 3)->nullable()->after('size')->index();
            $table->decimal('outer_diameter', 10, 3)->nullable()->after('inner_diameter')->index();
            $table->decimal('width', 10, 3)->nullable()->after('outer_diameter')->index();
            $table->string('brand', 50)->nullable()->after('width')->index();
            $table->string('material', 50)->nullable()->after('brand')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['inner_diameter']);
            $table->dropIndex(['outer_diameter']);
            $table->dropIndex(['width']);
            $table->dropIndex(['brand']);
            $table->dropIndex(['material']);
            $table->dropColumn(['inner_diameter', 'outer_diameter', 'width', 'brand', 'material']);
        });
    }
};
