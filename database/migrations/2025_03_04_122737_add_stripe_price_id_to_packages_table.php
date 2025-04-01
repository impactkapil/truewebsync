<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripePriceIdToPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('packages', function (Blueprint $table) {
        $table->string('stripe_price_id')->nullable()->after('price');
    });
}

public function down()
{
    Schema::table('packages', function (Blueprint $table) {
        $table->dropColumn('stripe_price_id');
    });
}

}
