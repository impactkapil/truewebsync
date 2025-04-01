<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Mail\CustomerVerificationMail;
use Illuminate\Support\Facades\Mail;


class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Customer::query()->with(['activePackage.package']);

        // Search by name or email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Exclude soft-deleted customers
        $customers = $query->paginate(10)->appends($request->all());

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:customers,email',
            'password'   => 'required|string|min:6|confirmed',
            'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'     => 'required|boolean',
        ]);

        // Capture the plain password before hashing
        $plainPassword = $validated['password'];

        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Hash the password before storing
        $validated['password'] = bcrypt($validated['password']);

        // Create the customer
        $customer = Customer::create($validated);

        // Send verification email
        Mail::to($customer->email)->send(new CustomerVerificationMail($customer, $plainPassword));

        // Redirect with success message
        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully and verification email sent.');
    }


    /**
     * Show the form for editing the specified customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\View\View
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Customer $customer)
    {
        // Validate the request data
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:customers,email,' . $customer->id,
            'password'   => 'nullable|string|min:6|confirmed',
            'avatar'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status'     => 'required|boolean',
        ]);

        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($customer->avatar) {
                Storage::disk('public')->delete($customer->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Hash the password if it's being updated
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Update the customer
        $customer->update($validated);

        // Redirect with success message
        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage (soft delete).
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Customer $customer)
    {
        // Delete avatar if exists
        if ($customer->avatar) {
            Storage::disk('public')->delete($customer->avatar);
        }

        // Soft delete the customer
        $customer->delete();

        // Redirect with success message
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully.');
    }

    /**
     * Toggle the status of the specified customer.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus(Customer $customer)
    {
        $customer->status = !$customer->status;
        $customer->save();

        return redirect()->route('admin.customers.index')->with('success', 'Customer status updated successfully.');
    }

    /**
     * Handle bulk deletion of customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:customers,id',
        ]);

        $customers = Customer::whereIn('id', $request->ids)->get();

        foreach ($customers as $customer) {
            // Delete avatar if exists
            if ($customer->avatar) {
                Storage::disk('public')->delete($customer->avatar);
            }

            // Soft delete the customer
            $customer->delete();
        }
        Log::info('Bulk delete completed.');
        return redirect()->route('admin.customers.index')->with('success', 'Selected customers have been deleted successfully.');
    }
}
