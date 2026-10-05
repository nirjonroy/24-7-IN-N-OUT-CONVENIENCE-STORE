<div class="card-body">
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Label <span class="text-danger">*</span></label><input name="label" value="{{ old('label', $item->label) }}" class="form-control @error('label') is-invalid @enderror" required>@error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Link Type <span class="text-danger">*</span></label><select name="link_type" class="form-select"><option value="page" @selected(old('link_type', $item->link_type) === 'page')>Page</option><option value="custom" @selected(old('link_type', $item->link_type) === 'custom')>Custom</option></select>@error('link_type')<div class="text-danger small">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Parent Item</label><select name="parent_id" class="form-select"><option value="">Top level</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $item->parent_id) == $parent->id)>{{ $parent->label }}</option>@endforeach</select>@error('parent_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Page</label><select name="page_id" class="form-select"><option value="">Select Page</option>@foreach($pages as $page)<option value="{{ $page->id }}" @selected(old('page_id', $item->page_id) == $page->id)>{{ $page->name }} ({{ $page->slug }})</option>@endforeach</select>@error('page_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label class="form-label">Custom URL</label><input name="url" value="{{ old('url', $item->url) }}" class="form-control @error('url') is-invalid @enderror" placeholder="/contact, #services, https://example.com">@error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">Icon</label><input name="icon" value="{{ old('icon', $item->icon) }}" class="form-control @error('icon') is-invalid @enderror" placeholder="phone">@error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Badge</label><input name="badge" value="{{ old('badge', $item->badge) }}" class="form-control"></div>
    <div class="col-md-3 mb-3"><label class="form-label">Target</label><select name="target" class="form-select"><option value="_self" @selected(old('target', $item->target ?? '_self') === '_self')>_self</option><option value="_blank" @selected(old('target', $item->target) === '_blank')>_blank</option></select>@error('target')<div class="text-danger small">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Rel</label><input name="rel" value="{{ old('rel', $item->rel) }}" class="form-control"></div>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">CSS Identifier</label><input name="css_identifier" value="{{ old('css_identifier', $item->css_identifier) }}" class="form-control @error('css_identifier') is-invalid @enderror" placeholder="services-menu">@error('css_identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="form-control"></div>
    <div class="col-md-4 mb-3 d-flex align-items-end"><div class="form-check form-switch mb-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Active</label></div></div>
  </div>
  @include('admin.partials.seo-fields', ['model' => $item])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Menu Item</button><a href="{{ route('admin.menus.items.index', $menu) }}" class="btn btn-secondary">Cancel</a></div>
