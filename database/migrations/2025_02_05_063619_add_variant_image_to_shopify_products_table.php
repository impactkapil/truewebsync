<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            // Add a nullable string column for variant_image
            $table->string('variant_image')->nullable()->after('variant_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            $table->dropColumn('variant_image');
        });
    }
};
