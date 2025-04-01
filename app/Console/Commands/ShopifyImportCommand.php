<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShopifyStore;
use App\Jobs\ImportShopifyProductsJob;

class ShopifyImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     * e.g. 'php artisan shopify:import-products'
     */
    protected $signature = 'shopify:import-products';

    /**
     * The console command description.
     */
    protected $description = 'Dispatch a job to import products for each Shopify store every 5 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1) Query all your stores (or just a subset if needed)
        $stores = ShopifyStore::all();

        // 2) For each store, dispatch ImportShopifyProductsJob
        foreach ($stores as $store) {
            ImportShopifyProductsJob::dispatch($store, $store->customer_id);
        }

        $this->info('ImportShopifyProductsJob dispatched for ' . $stores->count() . ' store(s).');
    }
}
