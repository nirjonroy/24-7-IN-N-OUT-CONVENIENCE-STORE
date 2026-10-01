<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\SpecialBusinessHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SpecialBusinessHourController extends Controller
{
    public function index(Location $location): View
    {
        return view('admin.special-hours.index', [
            'location' => $location->load('business'),
            'specialHours' => $location->specialBusinessHours()->orderByDesc('date')->paginate(10),
        ]);
    }

    public function create(Location $location): View
    {
        return view('admin.special-hours.create', [
            'location' => $location->load('business'),
            'specialHour' => new SpecialBusinessHour(),
        ]);
    }

    public function store(Request $request, Location $location): RedirectResponse
    {
        $validated = $this->validatedData($request, $location);
        $location->specialBusinessHours()->create($validated);

        return redirect()->route('admin.locations.special-hours.index', $location)->with('success', 'Special business hour created successfully.');
    }

    public function edit(Location $location, SpecialBusinessHour $specialHour): View
    {
        abort_unless($specialHour->location_id === $location->id, 404);

        return view('admin.special-hours.edit', compact('location', 'specialHour'));
    }

    public function update(Request $request, Location $location, SpecialBusinessHour $specialHour): RedirectResponse
    {
        abort_unless($specialHour->location_id === $location->id, 404);
        $specialHour->update($this->validatedData($request, $location, $specialHour));

        return redirect()->route('admin.locations.special-hours.index', $location)->with('success', 'Special business hour updated successfully.');
    }

    public function destroy(Location $location, SpecialBusinessHour $specialHour): RedirectResponse
    {
        abort_unless($specialHour->location_id === $location->id, 404);
        $specialHour->delete();

        return back()->with('success', 'Special business hour deleted successfully.');
    }

    private function validatedData(Request $request, Location $location, ?SpecialBusinessHour $specialHour = null): array
    {
        $validated = $request->validate([
            'date' => ['required', 'date', Rule::unique('special_business_hours')->where('location_id', $location->id)->ignore($specialHour)],
            'opens_at' => ['nullable', 'date_format:H:i'],
            'closes_at' => ['nullable', 'date_format:H:i'],
            'is_closed' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['is_closed'] = $request->boolean('is_closed');

        if ($validated['is_closed']) {
            $validated['opens_at'] = null;
            $validated['closes_at'] = null;
        }

        return $validated;
    }
}
