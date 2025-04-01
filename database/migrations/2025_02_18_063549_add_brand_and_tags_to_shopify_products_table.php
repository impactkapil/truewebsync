<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('currency_symbol');
            $table->text('tags')->nullable()->after('brand');
        });
    }

    public function down()
    {
        Schema::table('shopify_products', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('tags');
        });
    }
};
