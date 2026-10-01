<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(Business $business): View
    {
        return view('admin.social-links.index', [
            'business' => $business,
            'socialLinks' => $business->socialLinks()->orderBy('sort_order')->paginate(10),
        ]);
    }

    public function create(Business $business): View
    {
        return view('admin.social-links.create', [
            'business' => $business,
            'socialLink' => new SocialLink(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        $business->socialLinks()->create($this->validatedData($request));

        return redirect()->route('admin.businesses.social-links.index', $business)->with('success', 'Social link created successfully.');
    }

    public function edit(Business $business, SocialLink $socialLink): View
    {
        abort_unless($socialLink->business_id === $business->id, 404);

        return view('admin.social-links.edit', compact('business', 'socialLink'));
    }

    public function update(Request $request, Business $business, SocialLink $socialLink): RedirectResponse
    {
        abort_unless($socialLink->business_id === $business->id, 404);
        $socialLink->update($this->validatedData($request));

        return redirect()->route('admin.businesses.social-links.index', $business)->with('success', 'Social link updated successfully.');
    }

    public function destroy(Business $business, SocialLink $socialLink): RedirectResponse
    {
        abort_unless($socialLink->business_id === $business->id, 404);
        $socialLink->delete();

        return back()->with('success', 'Social link deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:100'],
            'url' => ['required', 'url'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
            'page_name' => ['nullable', 'string', 'max:150'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_image' => ['nullable', 'string', 'max:500'],
            'author' => ['nullable', 'string', 'max:150'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'site_name' => ['nullable', 'string', 'max:150'],
            'keywords' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
