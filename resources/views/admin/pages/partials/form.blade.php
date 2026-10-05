<div class="card-body">
  <h5>Page Information</h5>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Page Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $page->name) }}" class="form-control @error('name') is-invalid @enderror" required />@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Slug</label><input name="slug" value="{{ old('slug', $page->slug) }}" class="form-control @error('slug') is-invalid @enderror" />@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Template <span class="text-danger">*</span></label><input name="template" value="{{ old('template', $page->template ?: 'default') }}" class="form-control @error('template') is-invalid @enderror" required />@error('template')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="row">
    <div class="col-md-8 mb-3"><label class="form-label">H1 <span class="text-danger">*</span></label><input name="h1" value="{{ old('h1', $page->h1) }}" class="form-control @error('h1') is-invalid @enderror" required />@error('h1')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Route Name</label><input name="route_name" value="{{ old('route_name', $page->route_name) }}" class="form-control" /></div>
  </div>
  <div class="mb-3"><label class="form-label">Intro Text</label><textarea name="intro_text" rows="3" class="form-control">{{ old('intro_text', $page->intro_text) }}</textarea></div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Status</label><select name="status" class="form-select">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $page->status ?: 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" min="0" value="{{ old('sort_order', $page->sort_order ?? 0) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Published At</label><input type="datetime-local" name="published_at" value="{{ old('published_at', optional($page->published_at)->format('Y-m-d\TH:i')) }}" class="form-control" /></div>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3"><div class="form-check form-switch"><input type="hidden" name="is_home" value="0" /><input class="form-check-input" type="checkbox" name="is_home" value="1" id="is_home" {{ old('is_home', $page->is_home) ? 'checked' : '' }} /><label class="form-check-label" for="is_home">Home Page</label></div></div>
    <div class="col-md-4 mb-3"><div class="form-check form-switch"><input type="hidden" name="robots_index" value="0" /><input class="form-check-input" type="checkbox" name="robots_index" value="1" id="robots_index" {{ old('robots_index', $page->robots_index ?? true) ? 'checked' : '' }} /><label class="form-check-label" for="robots_index">Robots Index</label></div></div>
    <div class="col-md-4 mb-3"><div class="form-check form-switch"><input type="hidden" name="robots_follow" value="0" /><input class="form-check-input" type="checkbox" name="robots_follow" value="1" id="robots_follow" {{ old('robots_follow', $page->robots_follow ?? true) ? 'checked' : '' }} /><label class="form-check-label" for="robots_follow">Robots Follow</label></div></div>
  </div>
  @include('admin.partials.seo-fields', ['model' => $page])
  <hr />
  <h5>Advanced SEO</h5>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Canonical URL</label><input name="canonical_url" value="{{ old('canonical_url', $page->canonical_url) }}" class="form-control @error('canonical_url') is-invalid @enderror" />@error('canonical_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label class="form-label">Open Graph Image</label><input name="og_image" value="{{ old('og_image', $page->og_image) }}" class="form-control" /></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Open Graph Title</label><input name="og_title" value="{{ old('og_title', $page->og_title) }}" class="form-control" /></div>
    <div class="col-md-6 mb-3"><label class="form-label">Open Graph Description</label><textarea name="og_description" rows="2" class="form-control">{{ old('og_description', $page->og_description) }}</textarea></div>
  </div>
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Page</button><a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Cancel</a></div>
