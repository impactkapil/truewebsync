<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_name')->unique();
            $table->unsignedInteger('number_of_shops');
            $table->unsignedInteger('number_of_products');
            $table->unsignedInteger('orders');
            $table->unsignedInteger('manage_customers');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('locations');
            $table->boolean('status')->default(true); // true for active, false for deactivated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
}
