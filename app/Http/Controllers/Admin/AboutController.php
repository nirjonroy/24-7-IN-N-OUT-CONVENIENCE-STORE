<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function edit(): View
    {
        return view('admin.about.edit', [
            'about' => About::first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $about = About::firstOrNew();

        $validated = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description_one' => ['nullable', 'string'],
            'description_two' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'identity_eyebrow' => ['nullable', 'string', 'max:255'],
            'identity_title' => ['nullable', 'string', 'max:255'],
            'identity_description' => ['nullable', 'string'],
            'category_one_label' => ['nullable', 'string', 'max:255'],
            'category_one_title' => ['nullable', 'string', 'max:255'],
            'category_two_label' => ['nullable', 'string', 'max:255'],
            'category_two_title' => ['nullable', 'string', 'max:255'],
            'category_three_label' => ['nullable', 'string', 'max:255'],
            'category_three_title' => ['nullable', 'string', 'max:255'],
            'category_four_label' => ['nullable', 'string', 'max:255'],
            'category_four_title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
        ]);

        $validated['status'] = $request->boolean('status');

        if ($request->hasFile('image')) {
            if ($about->image) {
                Storage::disk('public')->delete($about->image);
            }

            $validated['image'] = $request->file('image')->store('abouts', 'public');
        } else {
            unset($validated['image']);
        }

        $about->fill($validated)->save();

        return redirect()
            ->route('admin.about.edit')
            ->with('success', 'About content saved successfully.');
    }
}
