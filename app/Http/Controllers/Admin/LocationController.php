<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.locations.index', [
            'locations' => Location::with('business')
                ->when($request->filled('business_id'), fn ($query) => $query->where('business_id', $request->business_id))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'businesses' => Business::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.locations.create', [
            'location' => new Location(['country_code' => 'US', 'is_active' => true]),
            'businesses' => Business::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated) {
            if ($validated['is_primary']) {
                Location::where('business_id', $validated['business_id'])->update(['is_primary' => false]);
            }

            Location::create($validated);
        });

        return redirect()->route('admin.locations.index')->with('success', 'Location created successfully.');
    }

    public function show(Location $location): RedirectResponse
    {
        return redirect()->route('admin.locations.edit', $location);
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.edit', [
            'location' => $location,
            'businesses' => Business::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $this->validatedData($request, $location);

        DB::transaction(function () use ($location, $validated) {
            if ($validated['is_primary']) {
                Location::where('business_id', $validated['business_id'])
                    ->whereKeyNot($location->id)
                    ->update(['is_primary' => false]);
            }

            $location->update($validated);
        });

        return redirect()->route('admin.locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Location deleted successfully.');
    }

    private function validatedData(Request $request, ?Location $location = null): array
    {
        $businessId = $request->input('business_id');

        $validated = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:180', Rule::unique('locations')->where('business_id', $businessId)->ignore($location)],
            'phone' => ['nullable', 'string', 'max:30'],
            'secondary_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country_code' => ['required', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'price_range' => ['nullable', 'string', 'max:50'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'google_business_url' => ['nullable', 'url'],
            'google_maps_url' => ['nullable', 'url'],
            'directions_url' => ['nullable', 'url'],
            'is_primary' => ['nullable', 'boolean'],
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

        $validated['country_code'] = strtoupper($validated['country_code']);
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
