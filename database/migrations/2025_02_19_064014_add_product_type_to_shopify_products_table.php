<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductTypeToShopifyProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            // Add the product_type column after the 'tags' column; adjust as needed.
            $table->string('product_type')->nullable()->after('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
    }
}
