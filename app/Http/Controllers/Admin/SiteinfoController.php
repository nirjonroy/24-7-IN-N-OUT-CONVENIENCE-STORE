<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siteinfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteinfoController extends Controller
{
    public function edit(): View
    {
        return view('admin.siteinfo.edit', [
            'siteinfo' => Siteinfo::first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $siteinfo = Siteinfo::firstOrNew();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,jpg,jpeg,png,webp,svg', 'max:1024'],
        ]);

        foreach (['logo', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                if ($siteinfo->{$field}) {
                    Storage::disk('public')->delete($siteinfo->{$field});
                }

                $validated[$field] = $request->file($field)->store('siteinfo', 'public');
            } else {
                unset($validated[$field]);
            }
        }

        $siteinfo->fill($validated)->save();

        return redirect()
            ->route('admin.siteinfo.edit')
            ->with('success', 'Site info saved successfully.');
    }
}
