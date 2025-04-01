<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the packages.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $packages = Package::all();
        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.packages.create');
    }

    /**
     * Store a newly created package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'package_name' => 'required|string|unique:packages,package_name',
            'number_of_shops' => 'required|integer|min:1',
            'number_of_products' => 'required|integer|min:1',
            'orders' => 'required|integer|min:1',
            'manage_customers' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'locations' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        // Create the package
        Package::create($validated);

        // Redirect with success message
        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    /**
     * Show the form for editing the specified package.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\View\View
     */
    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    /**
     * Update the specified package in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Package $package)
    {
        // Validate the request data
        $validated = $request->validate([
            'package_name' => 'required|string|unique:packages,package_name,' . $package->id,
            'number_of_shops' => 'required|integer|min:1',
            'number_of_products' => 'required|integer|min:1',
            'orders' => 'required|integer|min:1',
            'manage_customers' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'locations' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        // Update the package
        $package->update($validated);

        // Redirect with success message
        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified package from storage.
     *
     * @param  \App\Models\Package  $package
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Package $package)
    {
        $package->delete();

        // Redirect with success message
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully.');
    }
}
