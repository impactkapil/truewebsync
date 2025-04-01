<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shopify_products', function (Blueprint $table) {
            $table->id();

            // If you want to link to a "customer" who owns this product listing
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            // Link to the local "shopify_stores" table if you have one
            $table->unsignedBigInteger('shopify_store_id')->nullable()->index();

            // The numeric product ID from Shopify
            $table->unsignedBigInteger('product_id')->index();

            // Title of the product
            $table->string('product_title')->nullable();

            // Variant-specific fields
            $table->string('variant_name')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->string('variant_sku')->nullable();
            
            // Use decimal(10,2) to store prices safely
            $table->decimal('variant_price', 10, 2)->nullable();

            // Store inventory data as JSON or text
            $table->json('variant_inventory')->nullable();

            // location_ids is cast to array in the model, so store as JSON
            $table->json('location_ids')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify_products');
    }
};
