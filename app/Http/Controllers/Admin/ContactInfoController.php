<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactInfoController extends Controller
{
    public function edit(): View
    {
        return view('admin.contact-info.edit', [
            'contactInfo' => ContactInfo::first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $contactInfo = ContactInfo::firstOrNew();

        $validated = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address_title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'google_map_text' => ['nullable', 'string', 'max:255'],
            'google_map_url' => ['nullable', 'string'],
            'business_details_title' => ['nullable', 'string', 'max:255'],
            'business_details_description' => ['nullable', 'string'],
            'business_profile_button_text' => ['nullable', 'string', 'max:255'],
            'business_profile_url' => ['nullable', 'string'],
            'map_embed_url' => ['nullable', 'string'],
            'form_eyebrow' => ['nullable', 'string', 'max:255'],
            'form_title' => ['nullable', 'string', 'max:255'],
            'form_description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ]);

        $validated['status'] = $request->boolean('status');

        $contactInfo->fill($validated)->save();

        return redirect()
            ->route('admin.contact-info.edit')
            ->with('success', 'Contact info saved successfully.');
    }
}
