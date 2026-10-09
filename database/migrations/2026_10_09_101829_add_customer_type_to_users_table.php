<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Null until the customer picks private person or company at checkout
     * or on the profile. The existing customers with a company name become
     * companies; the rest pick at their next checkout.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('customer_type')->nullable()->after('phone');
        });

        DB::table('users')
            ->whereNotNull('billing_company_name')
            ->where('billing_company_name', '!=', '')
            ->update(['customer_type' => 'company']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('customer_type');
        });
    }
};
