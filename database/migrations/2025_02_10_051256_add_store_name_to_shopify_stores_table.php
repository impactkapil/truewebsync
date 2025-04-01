<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreNameToShopifyStoresTable extends Migration
{
    public function up()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->string('store_name')->after('customer_id');
        });
    }

    public function down()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->dropColumn('store_name');
        });
    }
}
