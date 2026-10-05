<div class="card-body">
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Category</label><select name="gallery_category_id" class="form-select"><option value="">Uncategorized</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('gallery_category_id', $item->gallery_category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select>@error('gallery_category_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Title</label><input name="title" value="{{ old('title', $item->title) }}" class="form-control @error('title') is-invalid @enderror">@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Taken At</label><input type="date" name="taken_at" value="{{ old('taken_at', optional($item->taken_at)->format('Y-m-d')) }}" class="form-control @error('taken_at') is-invalid @enderror">@error('taken_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="mb-3"><label class="form-label">Caption</label><textarea name="caption" rows="2" class="form-control">{{ old('caption', $item->caption) }}</textarea></div>
  <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="4" class="form-control">{{ old('description', $item->description) }}</textarea></div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Photographer</label><input name="photographer" value="{{ old('photographer', $item->photographer) }}" class="form-control"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Source URL</label><input name="source_url" value="{{ old('source_url', $item->source_url) }}" class="form-control @error('source_url') is-invalid @enderror">@error('source_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="form-control"></div>
  </div>
  <div class="row"><div class="col-md-6"><div class="form-check form-switch mb-3"><input type="hidden" name="is_featured" value="0"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured', $item->is_featured) ? 'checked' : '' }}><label class="form-check-label" for="is_featured">Featured</label></div></div><div class="col-md-6"><div class="form-check form-switch mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Active</label></div></div></div>
  @include('admin.components.media-picker', ['model' => $item, 'collection' => 'image', 'label' => 'Gallery Image', 'fieldName' => 'image_media_id', 'altFieldName' => 'image_alt_override'])
  @error('image_media_id')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
  @include('admin.partials.seo-fields', ['model' => $item, 'mediaModel' => $item, 'mediaCollections' => ['meta_image']])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Item</button><a href="{{ route('admin.gallery.items.index') }}" class="btn btn-secondary">Cancel</a></div>
