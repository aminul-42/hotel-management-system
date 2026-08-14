<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    // Keys that are file uploads, handled separately from plain text/number fields
    private const IMAGE_KEYS = ['app_logo', 'app_favicon', 'hero_banner_image'];

    public function index()
    {
        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:150',
            'contact_email' => 'required|email|max:150',
            'contact_phone' => 'required|string|max:30',
            'hero_tagline' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
            'vat_percentage' => 'required|numeric|min:0|max:100',
            'service_charge_percentage' => 'required|numeric|min:0|max:100',
            'deposit_percentage' => 'required|numeric|min:0|max:100',
            'free_cancellation_hours' => 'required|integer|min:0',
            'partial_refund_percentage' => 'required|numeric|min:0|max:100',

            'app_logo' => 'nullable|image|max:1024',
            'app_favicon' => 'nullable|image|max:512',
            'hero_banner_image' => 'nullable|image|max:2048',

            'remove_app_logo' => 'nullable',
            'remove_app_favicon' => 'nullable',
            'remove_hero_banner_image' => 'nullable',
        ]);

        // Plain text/number fields — save as-is
        foreach ($validated as $key => $value) {
            if (in_array($key, self::IMAGE_KEYS) || str_starts_with($key, 'remove_')) {
                continue;
            }
            Setting::set($key, $value);
        }

        // Image fields — handle upload / removal per key
        foreach (self::IMAGE_KEYS as $key) {
            if ($request->hasFile($key)) {
                $old = Setting::get($key);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file($key)->store('settings', 'public');
                Setting::set($key, $path);
            } elseif ($request->boolean("remove_{$key}")) {
                $old = Setting::get($key);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                Setting::set($key, null);
            }
        }

        return response()->json(['message' => 'Settings updated successfully.']);
    }
}