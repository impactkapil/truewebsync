<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Signifly\Shopify\Shopify;
use App\Models\ShopifyStore;
use App\Models\SelectedProduct;
use App\Models\UserPackage;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
    /**
     * Show the Customer Dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function __construct()
    {
        $this->middleware(['auth:customer', 'verified']);
    }

    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $storeCount = ShopifyStore::where('customer_id', $customer->id)->count();
        $productCount = SelectedProduct::where('customer_id', $customer->id)->count();

        $activeUserPackage = UserPackage::where('customer_id', $customer->id)
            ->where('status', 1)
            ->with('package')
            ->first();
        $packageName = $activeUserPackage && $activeUserPackage->package
            ? $activeUserPackage->package->package_name
            : 'N/A';

       
        $storesByDate = ShopifyStore::where('customer_id', $customer->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $productsByDate = SelectedProduct::where('customer_id', $customer->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $allDates = collect(array_keys($storesByDate))
            ->merge(array_keys($productsByDate))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $storeChartData = [];
        $productChartData = [];
        foreach ($allDates as $d) {
            $storeChartData[]   = $storesByDate[$d]   ?? 0;
            $productChartData[] = $productsByDate[$d] ?? 0;
        }

        return view('customer.dashboard', [
            'storeCount'       => $storeCount,
            'productCount'     => $productCount,
            'packageName'      => $packageName,
            'chartLabels'      => $allDates,           // e.g. ['2025-01-01','2025-01-02',...]
            'storeChartData'   => $storeChartData,     // e.g. [2,1,...]
            'productChartData' => $productChartData,   // e.g. [5,0,...]
        ]);
    }


    public function purchases(Request $request, Shopify $shopify)
    {
        $customer = Auth::guard('customer')->user();

        // Retrieve search and per_page from the query parameters (with defaults)
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        // Build the query on userPackages
        $query = $customer->userPackages()->with('package');

        // If a search term is present, filter the results
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search in related package fields
                $q->whereHas('package', function ($q2) use ($search) {
                    $q2->where('package_name', 'like', '%' . $search . '%')
                        ->orWhere('price', 'like', '%' . $search . '%')
                        ->orWhere('number_of_shops', 'like', '%' . $search . '%')
                        ->orWhere('number_of_products', 'like', '%' . $search . '%')
                        ->orWhere('orders', 'like', '%' . $search . '%')
                        ->orWhere('manage_customers', 'like', '%' . $search . '%')
                        ->orWhere('locations', 'like', '%' . $search . '%');
                })
                // Also search in user_packages table fields if needed
                ->orWhere('status', 'like', '%' . $search . '%')
                ->orWhere('expiry_date', 'like', '%' . $search . '%');
            });
        }

        // Sort by purchase date (created_at) descending
        $query->orderBy('created_at', 'desc');

        // Paginate the results, preserving query parameters for links
        $userPackages = $query->paginate($perPage)->withQueryString();

        // Pass data to the view
        return view('customer.purchases', [
            'userPackages' => $userPackages,
            'search'       => $search,
            'perPage'      => $perPage
        ]);
    }
}
