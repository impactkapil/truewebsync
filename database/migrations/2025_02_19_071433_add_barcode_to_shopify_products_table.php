<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBarcodeToShopifyProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            // Add the barcode column after the product_type column (adjust as needed).
            $table->string('barcode')->nullable()->after('product_type');
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
            $table->dropColumn('barcode');
        });
    }
}
