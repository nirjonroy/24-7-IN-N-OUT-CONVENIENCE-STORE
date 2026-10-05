<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.reviews.index', [
            'reviews' => Review::with('mediaAttachments.media.variants')
                ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query
                    ->where('author_name', 'like', '%'.$request->search.'%')
                    ->orWhere('review_text', 'like', '%'.$request->search.'%')
                    ->orWhere('external_id', 'like', '%'.$request->search.'%')))
                ->when($request->filled('source'), fn ($query) => $query->where('source', $request->source))
                ->when($request->filled('rating'), fn ($query) => $query->where('rating', $request->rating))
                ->when($request->filled('featured'), fn ($query) => $query->where('is_featured', $request->featured === '1'))
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'sources' => Review::SOURCES,
            'filters' => $request->only(['search', 'source', 'rating', 'featured', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.reviews.create', [
            'review' => new Review(['source' => 'manual', 'is_active' => true]),
            'sources' => Review::SOURCES,
        ]);
    }

    public function store(StoreReviewRequest $request, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        DB::transaction(fn () => $this->saveReview(new Review(), $request, $mediaAttachments));

        return redirect()->route('admin.reviews.index')->with('success', 'Review created successfully.');
    }

    public function show(Review $review): RedirectResponse
    {
        return redirect()->route('admin.reviews.edit', $review);
    }

    public function edit(Review $review): View
    {
        $review->load('mediaAttachments.media.variants');

        return view('admin.reviews.edit', [
            'review' => $review,
            'sources' => Review::SOURCES,
        ]);
    }

    public function update(UpdateReviewRequest $request, Review $review, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        DB::transaction(fn () => $this->saveReview($review, $request, $mediaAttachments));

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated successfully.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->mediaAttachments()->delete();
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }

    private function saveReview(Review $review, Request $request, MediaAttachmentService $mediaAttachments): void
    {
        $data = $request->validated();
        $media = [
            'avatar' => [$data['avatar_media_id'] ?? null, $data['avatar_alt_override'] ?? null],
            'meta_image' => [$data['meta_image_media_id'] ?? null, $data['meta_image_alt_override'] ?? null],
        ];

        unset($data['avatar_media_id'], $data['avatar_alt_override'], $data['meta_image_media_id'], $data['meta_image_alt_override']);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $review->fill($data)->save();

        foreach ($media as $collection => [$id, $alt]) {
            $mediaAttachments->syncSingle($review, $collection, $id ? (int) $id : null, [
                'alt_text_override' => $alt,
            ]);
        }
    }
}
