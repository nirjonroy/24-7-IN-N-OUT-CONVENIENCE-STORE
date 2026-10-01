<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(): View
    {
        return view('admin.businesses.index', [
            'businesses' => Business::withCount('locations')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.businesses.create', [
            'business' => new Business(['currency' => 'USD', 'is_active' => true]),
        ]);
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        Business::create($this->validatedData($request->validated()));

        return redirect()->route('admin.businesses.index')->with('success', 'Business created successfully.');
    }

    public function show(Business $business): RedirectResponse
    {
        return redirect()->route('admin.businesses.edit', $business);
    }

    public function edit(Business $business): View
    {
        return view('admin.businesses.edit', compact('business'));
    }

    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        $business->update($this->validatedData($request->validated()));

        return redirect()->route('admin.businesses.index')->with('success', 'Business updated successfully.');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $business->delete();

        return redirect()->route('admin.businesses.index')->with('success', 'Business deleted successfully.');
    }

    private function validatedData(array $validated): array
    {
        $schemaTypes = $validated['schema_types'] ?? null;
        $validated['schema_types'] = $schemaTypes
            ? array_values(array_filter(array_map('trim', explode(',', $schemaTypes))))
            : null;
        $validated['currency'] = strtoupper($validated['currency']);
        $validated['is_active'] = request()->boolean('is_active');

        return $validated;
    }
}
