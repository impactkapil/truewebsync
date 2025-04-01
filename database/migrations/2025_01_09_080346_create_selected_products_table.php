<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSelectedProductsTable extends Migration
{
    public function up()
    {
        Schema::create('selected_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('shopify_store_id'); // If you want to store which store it belongs to
            $table->string('product_id'); // Shopify product ID (could be numeric or the GraphQL GID)
            $table->string('product_title')->nullable(); // optional, store the title
            $table->timestamps();

            // Add foreign key constraints if needed
            $table->foreign('customer_id')
                  ->references('id')->on('customers')
                  ->onDelete('cascade');
            
            // If you have a shopify_stores table with ID
            $table->foreign('shopify_store_id')
                  ->references('id')->on('shopify_stores')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('selected_products');
    }
}
