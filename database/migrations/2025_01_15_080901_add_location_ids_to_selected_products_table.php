<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationIdsToSelectedProductsTable extends Migration
{
    public function up()
    {
        Schema::table('selected_products', function (Blueprint $table) {
            $table->json('location_ids')->nullable()->after('variant_inventory');
        });
    }

    public function down()
    {
        Schema::table('selected_products', function (Blueprint $table) {
            $table->dropColumn('location_ids');
        });
    }
}
