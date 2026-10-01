<div class="card-body">
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Platform <span class="text-danger">*</span></label><input name="platform" list="platforms" value="{{ old('platform', $socialLink->platform) }}" class="form-control @error('platform') is-invalid @enderror" required /><datalist id="platforms"><option>Facebook</option><option>Instagram</option><option>TikTok</option><option>YouTube</option><option>X</option><option>LinkedIn</option><option>Google Business Profile</option><option>Other</option></datalist>@error('platform')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Label</label><input name="label" value="{{ old('label', $socialLink->label) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Icon</label><input name="icon" value="{{ old('icon', $socialLink->icon) }}" class="form-control" /></div>
  </div>
  <div class="mb-3"><label class="form-label">URL <span class="text-danger">*</span></label><textarea name="url" rows="2" class="form-control @error('url') is-invalid @enderror" required>{{ old('url', $socialLink->url) }}</textarea>@error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Sort Order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $socialLink->sort_order ?? 0) }}" class="form-control" /></div>
    <div class="col-md-8 mb-3 d-flex align-items-end"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0" /><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_social" @checked(old('is_active', $socialLink->is_active ?? true)) /><label class="form-check-label" for="is_active_social">Active</label></div></div>
  </div>
  @include('admin.partials.seo-fields', ['model' => $socialLink])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Social Link</button><a href="{{ route('admin.businesses.social-links.index', $business) }}" class="btn btn-secondary">Cancel</a></div>
