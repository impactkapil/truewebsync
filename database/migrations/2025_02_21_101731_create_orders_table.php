<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shopify_store_id')->nullable(); // to link with your store
            $table->string('shopify_order_id')->unique();
            $table->string('order_number')->nullable();
            $table->string('order_name')->nullable();
            $table->string('email')->nullable();
            $table->decimal('total_price', 8, 2)->default(0);
            $table->dateTime('ordered_at')->nullable();
            $table->timestamps();

            // If you want a foreign key reference:
            $table->foreign('shopify_store_id')
                  ->references('id')
                  ->on('shopify_stores')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
