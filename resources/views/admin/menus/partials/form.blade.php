<div class="card-body">
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Menu Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $menu->name) }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Key</label><input name="key" value="{{ old('key', $menu->key) }}" class="form-control @error('key') is-invalid @enderror">@error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Location</label><input name="location" value="{{ old('location', $menu->location) }}" class="form-control @error('location') is-invalid @enderror" placeholder="header, mobile, footer_services">@error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="3" class="form-control">{{ old('description', $menu->description) }}</textarea></div>
  <div class="row"><div class="col-md-4 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $menu->sort_order ?? 0) }}" class="form-control"></div><div class="col-md-8 mb-3 d-flex align-items-end"><div class="form-check form-switch mb-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Active</label></div></div></div>
  @include('admin.partials.seo-fields', ['model' => $menu])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Menu</button><a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">Cancel</a></div>
