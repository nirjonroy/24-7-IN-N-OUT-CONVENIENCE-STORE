<div class="card-body">
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Slug</label><input name="slug" value="{{ old('slug', $category->slug) }}" class="form-control @error('slug') is-invalid @enderror">@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control"></div>
  </div>
  <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea></div>
  <div class="row"><div class="col-md-6"><div class="form-check form-switch mb-3"><input type="hidden" name="is_featured" value="0"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured', $category->is_featured) ? 'checked' : '' }}><label class="form-check-label" for="is_featured">Featured</label></div></div><div class="col-md-6"><div class="form-check form-switch mb-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Active</label></div></div></div>
  @include('admin.partials.seo-fields', ['model' => $category])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Category</button><a href="{{ route('admin.faq.categories.index') }}" class="btn btn-secondary">Cancel</a></div>
