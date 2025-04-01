<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CustomerController as CustomerController;
use App\Http\Controllers\Customer\Auth\LoginController as CustomerLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\VerificationController;
use App\Http\Controllers\Admin\PackageController; 
use App\Http\Controllers\PackageController as PackageBuyController; 
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ShopifyStoreController;
use App\Http\Controllers\AddShopifyStoreController;
use App\Http\Controllers\OrderWebhookController;
use App\Http\Controllers\ShopifyOrderController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\StripeWebhookController;
// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Login Routes
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    // Admin Dashboard (Protected)
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Route::get('/packages', [PackageController::class, 'index'])->name('packages');

        Route::resource('packages', PackageController::class);

        Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggleStatus');
        Route::delete('/customers/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulkDelete');
        Route::resource('customers', CustomerController::class);

    });
});

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Customer Login Routes
    Route::get('/login', [CustomerLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [CustomerLoginController::class, 'logout'])->name('logout');
    Route::get('/verification/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    // Customer Dashboard (Protected)
    Route::middleware(['auth:customer', 'verified'])->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/packages/purchase', [PackageBuyController::class, 'purchase'])->name('packages.purchase');
        
        Route::get('/purchases', [CustomerDashboardController::class, 'purchases'])->name('purchases');
        // Route::get('/stores', [CustomerDashboardController::class, 'stores'])->name('stores');
        Route::get('/shopify/auth', [ShopifyStoreController::class, 'auth'])->name('shopify.auth');
        Route::get('/shopify/redirect', [AddShopifyStoreController::class, 'redirectToShopify'])->name('shopify.redirect');
        Route::get('/shopify/callback', [AddShopifyStoreController::class, 'handleShopifyCallback'])->name('shopify.callback');
        Route::get('/shopify/add', [AddShopifyStoreController::class, 'create'])->name('shopify.create');
        Route::post('/shopify/store', [AddShopifyStoreController::class, 'store'])->name('shopify.store');
        Route::get('/shopify/stores', [AddShopifyStoreController::class, 'stores'])->name('shopify.stores');
        Route::get('/shopify/productsbyid/{store}', [AddShopifyStoreController::class, 'showProducts'])->name('shopify.productsbyid');
        Route::post('/shopify/products/add',[AddShopifyStoreController::class, 'addSelectedProduct'])->name('shopify.products.add');
        Route::get('/shopify/fetchSelectedProducts',[AddShopifyStoreController::class, 'fetchSelectedProducts'])->name('shopify.fetchSelectedProducts');
        Route::delete('/shopify/stores/{store}',[AddShopifyStoreController::class, 'destroyStore'])->name('shopify.stores.destroy');
        Route::delete('/shopify/selected-products/delete-multiple',[AddShopifyStoreController::class, 'deleteMultipleSelectedProducts'])->name('shopify.selectedProducts.deleteMultiple');
        Route::delete('/shopify/selected-products/{id}',[AddShopifyStoreController::class, 'deleteSelectedProduct'])->where('id', '[0-9]+')->name('shopify.selectedProducts.delete');
        Route::post('/shopify/selected-products/inventory-update',[AddShopifyStoreController::class, 'updateInventory'])->name('shopify.selectedProducts.updateInventory');
        Route::post('/link-products', [LinkController::class, 'store'])->name('products.link');
        Route::get('/linked-products', [LinkController::class, 'fetchLinkedProducts'])->name('shopify.fetchLinkedProducts');
        Route::get('/linked-products/list', [LinkController::class, 'index'])->name('linkProducts.list');
        Route::delete('/unlink-products', [LinkController::class, 'unlinkProducts'])->name('products.unlink');
        Route::get('/shopify/search-master-shop-products', [LinkController::class, 'searchMasterShopProducts'])->name('shopify.searchMasterShopProducts');
        Route::post('shopify/link-products', [LinkController::class, 'linkProducts'])->name('shopify.linkProducts');
        Route::post('/unlink-products', [LinkController::class, 'unlinkProducts'])->name('products.unlinkProducts');
        Route::get('/shopify/search-unlinked-products', [LinkController::class, 'searchUnlinkedProducts'])->name('shopify.searchUnlinkedProducts');
        Route::delete('/products/delete', [LinkController::class, 'destroyUnlinkedProduct'])->name('products.delete');
        Route::post('/shopify/sync/{storeId}', [AddShopifyStoreController::class, 'syncNow'])->name('shopify.sync');
        Route::get('/shopify/import/progress/{store}', [AddShopifyStoreController::class, 'showImportProgress'])->name('shopify.import.progress');
        
        Route::get('/shopify/import/status/{store}', [AddShopifyStoreController::class, 'getImportStatus'])->name('shopify.import.status');
        Route::get('/shopify/import/status2/{storeId}', [AddShopifyStoreController::class, 'getImportStatus2'])->name('shopify.import.status2');
        Route::post('/shopify/sync/v2/{storeId}', [AddShopifyStoreController::class, 'syncNowV2'])->name('shopify.sync.v2');
        Route::get('/shopify/stores/{id}/edit', [AddShopifyStoreController::class, 'editStore'])->name('shopify.stores.edit');
        Route::put('/shopify/stores/{id}', [AddShopifyStoreController::class, 'updateStore'])->name('shopify.stores.update');

        Route::get('/shopify/orders',[ShopifyOrderController::class, 'index'])->name('shopify.orders.index');
        

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/toggle/{id}', [SettingsController::class, 'toggle'])->name('settings.toggle');
        Route::get('/checkout/{package}', [SubscriptionController::class, 'showCheckoutForm'])->name('subscribe.form');
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
        Route::get('/billing-portal', [BillingController::class, 'redirectToBillingPortal'])->name('billing.portal');
        
        Route::post('/subscription/switch/{id}', [SubscriptionController::class, 'switchUserPackage'])->name('subscription.switch');

        Route::middleware(['subscription.active'])->group(function () {
            Route::get('/manage-subscriptions', [SubscriptionController::class, 'manageSubscriptions'])->name('subscription.manage');
            Route::post('/subscription/{id}/cancel', [SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel');
            Route::get('/subscription/status', [SubscriptionController::class, 'subscriptionStatus'])->name('subscription.status');
            Route::post('/subscription/swap', [SubscriptionController::class, 'swapSubscription'])->name('subscription.swap');
            Route::get('/subscription/swap', [SubscriptionController::class, 'showSwapOptions'])->name('subscription.swap.options');

        });
        Route::get('/manage-packages', [SubscriptionController::class, 'packages'])->name('subscription.packages');
        // Route::post('/subscription/switch/{subscription}/{priceId}', [SubscriptionController::class, 'switchSubscription'])->name('subscription.switchStripe');
        // Route::get('/subscription/subscribe/{package}', [SubscriptionController::class, 'subscribeForm'])->name('subscribe.form');
        Route::get('/subscription/expired', function () {
            return view('subscription.expired');
        })->name('subscription.expired');

        Route::get('/subscription/{subscription}/switch/{priceId}/confirm',[SubscriptionController::class, 'confirmSwitch'])->name('subscription.switchConfirm');

        Route::post('/subscription/{subscription}/switch/{priceId}',[SubscriptionController::class, 'switchSubscription'])->name('subscription.switchStripe');
        
        Route::get('/subscription/{subscription}/switch/{priceId}/complete',[SubscriptionController::class, 'completeSwitch'])->name('subscription.switchComplete');

    });

   
    
});
Route::post('/customer/shopify/webhook/orders', [OrderWebhookController::class, 'handleWebhook'])
     ->name('customer.shopify.webhook.orders');

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
});

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

Route::get('/', [FrontendController::class, 'homepage'])->name('homepage');
Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/packages', [FrontendController::class, 'packages'])->name('packages');
Auth::routes();
