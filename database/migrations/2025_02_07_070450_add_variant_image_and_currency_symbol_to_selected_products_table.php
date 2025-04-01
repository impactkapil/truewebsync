<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantImageAndCurrencySymbolToSelectedProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('selected_products', function (Blueprint $table) {
        // Adding the variant_image column (string type, nullable)
        $table->string('variant_image')->nullable()->after('variant_id');
        
        // Adding the currency_symbol column (string type, nullable)
        $table->string('currency_symbol')->nullable()->after('variant_price');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    Schema::table('selected_products', function (Blueprint $table) {
        // Dropping the new columns
        $table->dropColumn('variant_image');
        $table->dropColumn('currency_symbol');
    });
}
}
