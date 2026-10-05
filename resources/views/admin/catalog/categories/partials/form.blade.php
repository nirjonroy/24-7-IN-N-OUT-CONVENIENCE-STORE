<div class="card-body">
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Parent Category</label><select name="parent_id" class="form-select"><option value="">None</option>@foreach($categories as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>@endforeach</select>@error('parent_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Category Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Slug</label><input name="slug" value="{{ old('slug', $category->slug) }}" class="form-control @error('slug') is-invalid @enderror">@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Business Area</label><select name="business_area" class="form-select">@foreach($businessAreas as $area)<option value="{{ $area }}" @selected(old('business_area', $category->business_area) === $area)>{{ str_replace('_', ' ', ucfirst($area)) }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Minimum Age</label><input type="number" name="minimum_age" min="0" max="99" value="{{ old('minimum_age', $category->minimum_age) }}" class="form-control"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control"></div>
  </div>
  <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea></div>
  @include('admin.components.media-picker', ['model' => $category, 'collection' => 'image', 'label' => 'Category Image', 'fieldName' => 'image_media_id', 'altFieldName' => 'image_alt_override'])
  <div class="row"><div class="col-md-6"><div class="form-check form-switch mb-3"><input type="hidden" name="is_featured" value="0"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured', $category->is_featured) ? 'checked' : '' }}><label class="form-check-label" for="is_featured">Featured</label></div></div><div class="col-md-6"><div class="form-check form-switch mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Active</label></div></div></div>
  @include('admin.partials.seo-fields', ['model' => $category, 'mediaModel' => $category, 'mediaCollections' => ['meta_image']])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Category</button><a href="{{ route('admin.catalog.categories.index') }}" class="btn btn-secondary">Cancel</a></div>
