<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantFieldsToSelectedProductsTable extends Migration
{
    public function up()
    {
        Schema::table('selected_products', function (Blueprint $table) {
            $table->string('variant_id')->nullable()->after('product_title');
            $table->string('variant_sku')->nullable()->after('variant_id');
            $table->string('variant_price')->nullable()->after('variant_sku');
            $table->string('variant_inventory')->nullable()->after('variant_price');
        });
    }

    public function down()
    {
        Schema::table('selected_products', function (Blueprint $table) {
            $table->dropColumn('variant_id');
            $table->dropColumn('variant_sku');
            $table->dropColumn('variant_price');
            $table->dropColumn('variant_inventory');
        });
    }
}
