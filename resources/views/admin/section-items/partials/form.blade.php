@php
  $settingsValue = old('settings', is_array($item->settings) ? json_encode($item->settings, JSON_PRETTY_PRINT) : $item->settings);
@endphp
<div class="card-body">
  <h5>Item Information</h5>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Item Key</label><input name="item_key" value="{{ old('item_key', $item->item_key) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Title</label><input name="title" value="{{ old('title', $item->title) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Subtitle</label><input name="subtitle" value="{{ old('subtitle', $item->subtitle) }}" class="form-control" /></div>
  </div>
  <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="4" class="form-control">{{ old('description', $item->description) }}</textarea></div>
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">Badge</label><input name="badge" value="{{ old('badge', $item->badge) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Icon</label><input name="icon" value="{{ old('icon', $item->icon) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3 d-flex align-items-end"><div class="form-check form-switch mb-2"><input type="hidden" name="is_active" value="0" /><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }} /><label class="form-check-label" for="is_active">Active</label></div></div>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Image</label><input name="image" value="{{ old('image', $item->image) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Image Alt</label><input name="image_alt" value="{{ old('image_alt', $item->image_alt) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Settings JSON</label><textarea name="settings" rows="1" class="form-control @error('settings') is-invalid @enderror">{{ $settingsValue }}</textarea>@error('settings')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="form-text mb-3">Image path fields above remain as legacy / external URL fallbacks.</div>
  @include('admin.components.media-picker', ['model' => $item, 'collection' => 'image', 'label' => 'Item Image Media Library', 'fieldName' => 'image_media_id', 'altFieldName' => 'image_alt_override'])
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Button Label</label><input name="button_label" value="{{ old('button_label', $item->button_label) }}" class="form-control" /></div>
    <div class="col-md-6 mb-3"><label class="form-label">Button URL</label><input name="button_url" value="{{ old('button_url', $item->button_url) }}" class="form-control" /></div>
  </div>
  @include('admin.partials.seo-fields', ['model' => $item, 'mediaModel' => $item, 'mediaCollections' => ['meta_image']])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Item</button><a href="{{ route('admin.pages.sections.items.index', [$page, $section]) }}" class="btn btn-secondary">Cancel</a></div>
