<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class FrontendController extends Controller
{
    /**
     * Show the Homepage.
     *
     * @return \Illuminate\View\View
     */
    public function homepage()
    {
        return view('frontend.homepage');
    }

    /**
     * Show the About Page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('frontend.about');
    }

    /**
     * Show the Packages Page.
     *
     * @return \Illuminate\View\View
     */
    public function packages()
    {
        $packages = Package::where('status', true)->get(); // Only active packages
        return view('frontend.packages', compact('packages'));
    }
}
