<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = SystemSetting::current();

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $settings = SystemSetting::current();

        if ($request->hasFile('logo')) {
            $uploadsDir = public_path('uploads/settings');
            if (! is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0775, true);
            }

            $extension = $request->file('logo')->getClientOriginalExtension();
            $filename = 'system-' . uniqid() . '.' . $extension;
            $path = 'uploads/settings/' . $filename;

            $request->file('logo')->move($uploadsDir, $filename);

            $validated['logo_path'] = $path;
        }

        $settings->update([
            'school_name' => $validated['school_name'],
            'address' => $validated['address'] ?? null,
            'contact_info' => $validated['contact_info'] ?? null,
            'logo_path' => $validated['logo_path'] ?? $settings->logo_path,
        ]);

        return redirect()->route('settings.edit')->with('success', 'System settings updated successfully.');
    }
}

