<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('linked_products', function (Blueprint $table) {
            $table->id();

            // product_one_id and product_two_id reference the primary key
            // of the 'selected_products' table (where you store each chosen Shopify product).
            $table->unsignedBigInteger('product_one_id');
            $table->unsignedBigInteger('product_two_id');

            $table->timestamps();

            // Add foreign keys
            $table->foreign('product_one_id')
                  ->references('id')->on('selected_products')
                  ->onDelete('cascade');

            $table->foreign('product_two_id')
                  ->references('id')->on('selected_products')
                  ->onDelete('cascade');

            // Prevent duplicating the same pair (1,2) or (2,1).
            $table->unique(['product_one_id', 'product_two_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('linked_products');
    }
};
