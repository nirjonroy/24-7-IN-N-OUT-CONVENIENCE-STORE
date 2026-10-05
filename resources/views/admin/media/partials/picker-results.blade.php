<div class="row g-3">
@forelse($mediaAssets as $media)
  <div class="col-md-4">
    <button type="button" class="btn p-0 w-100 text-start border media-picker-select" data-media-id="{{ $media->id }}" data-media-url="{{ $media->getVariantUrl('thumbnail') }}">
      <img src="{{ $media->getVariantUrl('thumbnail') }}" class="w-100" style="height:110px;object-fit:cover" alt="" loading="lazy">
      <span class="d-block small p-2 text-truncate">{{ $media->title ?: $media->original_name }}</span>
    </button>
  </div>
@empty
  <div class="col-12"><p class="text-secondary mb-0">No active media found.</p></div>
@endforelse
</div>
