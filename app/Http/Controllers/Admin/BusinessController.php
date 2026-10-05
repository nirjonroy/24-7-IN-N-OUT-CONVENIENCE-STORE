<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use App\Services\MediaAttachmentService;
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

    public function store(StoreBusinessRequest $request, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        [$data, $media] = $this->splitMediaData($request->validated());
        $business = Business::create($this->validatedData($data));
        $this->syncMedia($business, $mediaAttachments, $media);

        return redirect()->route('admin.businesses.index')->with('success', 'Business created successfully.');
    }

    public function show(Business $business): RedirectResponse
    {
        return redirect()->route('admin.businesses.edit', $business);
    }

    public function edit(Business $business): View
    {
        $business->load('mediaAttachments.media.variants');

        return view('admin.businesses.edit', compact('business'));
    }

    public function update(UpdateBusinessRequest $request, Business $business, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        [$data, $media] = $this->splitMediaData($request->validated());
        $business->update($this->validatedData($data));
        $this->syncMedia($business, $mediaAttachments, $media);

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

    private function splitMediaData(array $validated): array
    {
        $media = [
            'meta_image' => ['id' => $validated['meta_image_media_id'] ?? null, 'alt' => $validated['meta_image_alt_override'] ?? null],
            'logo' => ['id' => $validated['logo_media_id'] ?? null, 'alt' => $validated['logo_alt_override'] ?? null],
            'favicon' => ['id' => $validated['favicon_media_id'] ?? null, 'alt' => $validated['favicon_alt_override'] ?? null],
        ];

        unset(
            $validated['meta_image_media_id'],
            $validated['meta_image_alt_override'],
            $validated['logo_media_id'],
            $validated['logo_alt_override'],
            $validated['favicon_media_id'],
            $validated['favicon_alt_override']
        );

        return [$validated, $media];
    }

    private function syncMedia(Business $business, MediaAttachmentService $mediaAttachments, array $media): void
    {
        foreach ($media as $collection => $values) {
            $mediaAttachments->syncSingle($business, $collection, $values['id'] ? (int) $values['id'] : null, [
                'alt_text_override' => $values['alt'],
            ]);
        }
    }
}
