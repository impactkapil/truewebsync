<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductTypeAndBarcodeToSelectedProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('selected_products', function (Blueprint $table) {
            // Adding product_type column (nullable) after an existing column.
            // Adjust the 'after' clause if needed based on your table structure.
            $table->string('product_type')->nullable()->after('tags');
            
            // Adding barcode column (nullable) after the product_type column.
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
        Schema::table('selected_products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'barcode']);
        });
    }
}
