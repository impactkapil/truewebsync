<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopifyStoresTable extends Migration
{
    public function up()
    {
        Schema::create('shopify_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('shopify_domain')->unique();
            $table->text('access_token');
            $table->boolean('status')->default(1); // 1 for active, 0 for inactive
            $table->timestamps();

            // Foreign key relationship to the customer table
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopify_stores');
    }
}

