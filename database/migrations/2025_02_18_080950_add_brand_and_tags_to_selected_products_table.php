<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('selected_products', function (Blueprint $table) {
            // Add a nullable string column for the brand.
            $table->string('brand')->nullable()->after('currency_symbol'); // Replace 'existing_column' with the column after which you want to add brand

            // Add a nullable text column for tags.
            $table->text('tags')->nullable()->after('brand');
        });
    }

    public function down()
    {
        Schema::table('selected_products', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('tags');
        });
    }
};
