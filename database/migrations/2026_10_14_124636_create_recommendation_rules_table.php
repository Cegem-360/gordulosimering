<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Category::class, 'source_category_id')->nullable()->constrained('product_categories')->cascadeOnDelete();
            $table->foreignIdFor(Category::class, 'target_category_id')->nullable()->constrained('product_categories')->cascadeOnDelete();
            $table->string('target_code_prefix')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_rules');
    }
};
