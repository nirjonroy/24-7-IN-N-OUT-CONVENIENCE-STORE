<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\MediaAsset;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.media.index', [
            'mediaAssets' => $this->filteredMedia($request)
                ->withCount('attachments')
                ->with('variants')
                ->paginate(24)
                ->withQueryString(),
            'filters' => $request->only(['search', 'type', 'status', 'sort']),
        ]);
    }

    public function create(): View
    {
        return view('admin.media.create');
    }

    public function store(StoreMediaRequest $request, MediaUploadService $uploader): RedirectResponse
    {
        $file = $request->file('file');
        $checksum = hash_file('sha256', $file->getRealPath());
        $duplicate = MediaAsset::active()->where('checksum', $checksum)->first();

        $media = $uploader->upload($file, $request->user()?->id, $request->only([
            'title', 'alt_text', 'caption', 'credit', 'source_url',
        ]));

        $message = $duplicate ? 'Identical media already exists. Existing media asset was reused.' : 'Media uploaded successfully.';

        return redirect()->route('admin.media.edit', $media)->with('success', $message);
    }

    public function show(MediaAsset $medium): View
    {
        $medium->load(['variants', 'attachments.mediable', 'uploadedBy']);

        return view('admin.media.show', ['media' => $medium]);
    }

    public function edit(MediaAsset $medium): View
    {
        return view('admin.media.edit', ['media' => $medium]);
    }

    public function update(UpdateMediaRequest $request, MediaAsset $medium): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $medium->update($data);

        return redirect()->route('admin.media.index')->with('success', 'Media updated successfully.');
    }

    public function destroy(MediaAsset $medium): RedirectResponse
    {
        $usageCount = $medium->attachments()->count();

        if ($usageCount > 0) {
            return redirect()->route('admin.media.index')
                ->with('error', "This media is currently used in {$usageCount} content locations. Detach or replace it before deleting.");
        }

        $medium->delete();

        return redirect()->route('admin.media.index')->with('success', 'Media deleted successfully.');
    }

    public function picker(Request $request): JsonResponse
    {
        $media = $this->filteredMedia($request)
            ->active()
            ->with('variants')
            ->paginate(12)
            ->withQueryString();

        return response()->json([
            'html' => view('admin.media.partials.picker-results', ['mediaAssets' => $media])->render(),
            'next_page_url' => $media->nextPageUrl(),
        ]);
    }

    private function filteredMedia(Request $request)
    {
        return MediaAsset::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->search.'%';
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', $search)
                        ->orWhere('original_name', 'like', $search)
                        ->orWhere('file_name', 'like', $search)
                        ->orWhere('alt_text', 'like', $search);
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('mime_type', 'like', $request->type.'/%'))
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->when($request->sort === 'oldest', fn ($query) => $query->oldest(), fn ($query) => $query->latest());
    }
}
