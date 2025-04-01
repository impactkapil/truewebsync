<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMasterToShopifyStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->boolean('is_master')->default(0)->after('status')->comment('1 if this is the master store, 0 otherwise');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->dropColumn('is_master');
        });
    }
}
