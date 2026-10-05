@php
  $attachments = isset($model) && $model && $model->relationLoaded('mediaAttachments')
      ? $model->mediaAttachments->where('collection', $collection)->sortBy('sort_order')->values()
      : collect();
  $ids = old($fieldName, $attachments->pluck('media_asset_id')->implode(','));
@endphp
<div class="border rounded p-3 mb-3" data-media-gallery data-picker-endpoint="{{ route('admin.media.picker') }}">
  <label class="form-label">{{ $label }}</label>
  <input type="hidden" name="{{ $fieldName }}" value="{{ $ids }}" data-media-gallery-input>
  <div class="d-flex gap-2 flex-wrap mb-2" data-media-gallery-list>
    @foreach($attachments as $attachment)
      <div class="position-relative border rounded p-1" data-media-gallery-item data-media-id="{{ $attachment->media_asset_id }}">
        <img src="{{ $attachment->media->getVariantUrl('thumbnail') }}" alt="" style="width:90px;height:70px;object-fit:cover" loading="lazy">
        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 py-0 px-1" data-media-gallery-remove>&times;</button>
      </div>
    @endforeach
  </div>
  <button type="button" class="btn btn-outline-primary btn-sm" data-media-gallery-open>Add from Media Library</button>
  <div class="form-text">Images are saved in the order shown. Removing one does not delete the media asset.</div>
</div>
