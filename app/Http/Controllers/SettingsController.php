<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting; // Make sure this model exists

class SettingsController extends Controller
{
    // Display all settings
    public function index()
    {
        $settings = Setting::all();
        return view('settings', compact('settings'));
    }

    // Toggle the is_enabled value for a given setting
    public function toggle($id)
    {
        $setting = Setting::findOrFail($id);
        // Toggle the value (if is_enabled is boolean, this works well)
        $setting->is_enabled = !$setting->is_enabled;
        $setting->updated_at = now();
        $setting->save();

        return redirect()->back();
    }
}
