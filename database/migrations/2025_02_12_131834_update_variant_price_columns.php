<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // selected_products
        DB::statement('ALTER TABLE `selected_products` MODIFY `variant_price` DECIMAL(12,2)');
        
        // shopify_products
        DB::statement('ALTER TABLE `shopify_products` MODIFY `variant_price` DECIMAL(12,2)');
    }

    public function down()
    {
        // Revert back if needed
        DB::statement('ALTER TABLE `selected_products` MODIFY `variant_price` DECIMAL(10,2)');
        DB::statement('ALTER TABLE `shopify_products` MODIFY `variant_price` DECIMAL(10,2)');
    }
};
