<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->unsignedInteger('total_products')->default(0)->after('access_token');
            $table->unsignedInteger('imported_products')->default(0)->after('total_products');
        });
    }

    public function down()
    {
        Schema::table('shopify_stores', function (Blueprint $table) {
            $table->dropColumn(['total_products', 'imported_products']);
        });
    }
};

