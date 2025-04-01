<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Signifly\Shopify\Shopify;

class DashboardController extends Controller
{
    /**
     * Show the Admin Dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // $shopify = app(Shopify::class);
        // $products = $shopify->getProducts();
        // dd($products);
        return view('admin.dashboard');
    }
}
