@php
  $fieldName = $fieldName ?? $collection.'_media_id';
  $altFieldName = $altFieldName ?? $collection.'_alt_override';
  $selectedId = old($fieldName);
  $attachment = null;
  if (! $selectedId && isset($model) && $model && $model->relationLoaded('mediaAttachments')) {
      $attachment = $model->mediaAttachments->firstWhere('collection', $collection);
      $selectedId = $attachment?->media_asset_id;
  }
  $selectedMedia = $attachment?->media;
  $previewUrl = $selectedMedia ? $selectedMedia->getVariantUrl('thumbnail') : null;
@endphp
<div class="media-picker border rounded p-3 mb-3" data-media-picker data-picker-endpoint="{{ route('admin.media.picker') }}">
  <label class="form-label">{{ $label }}</label>
  <input type="hidden" name="{{ $fieldName }}" value="{{ $selectedId }}" data-media-picker-input>
  <div class="d-flex gap-3 align-items-center flex-wrap">
    <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="width:120px;height:90px;overflow:hidden">
      <img src="{{ $previewUrl }}" alt="" class="{{ $previewUrl ? '' : 'd-none' }}" data-media-picker-preview style="width:100%;height:100%;object-fit:cover" loading="lazy">
      <span class="text-secondary small {{ $previewUrl ? 'd-none' : '' }}" data-media-picker-empty>No media</span>
    </div>
    <div class="flex-grow-1">
      <div class="mb-2"><button type="button" class="btn btn-outline-primary btn-sm" data-media-picker-open>Choose from Media Library</button> <button type="button" class="btn btn-outline-secondary btn-sm" data-media-picker-remove>Remove</button></div>
      <input type="text" name="{{ $altFieldName }}" value="{{ old($altFieldName, $attachment?->alt_text_override) }}" class="form-control form-control-sm" placeholder="Alt text override">
      <div class="form-text">Use meaningful alt text for foreground images. Leave decorative images blank.</div>
    </div>
  </div>
</div>
