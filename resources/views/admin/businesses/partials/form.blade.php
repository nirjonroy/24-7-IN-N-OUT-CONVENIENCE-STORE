<div class="card-body">
  <h5>General Information</h5>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Business Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $business->name) }}" class="form-control @error('name') is-invalid @enderror" required />@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Short Name</label><input name="short_name" value="{{ old('short_name', $business->short_name) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Currency</label><input name="currency" value="{{ old('currency', $business->currency ?: 'USD') }}" class="form-control @error('currency') is-invalid @enderror" maxlength="3" required />@error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Legal Name</label><input name="legal_name" value="{{ old('legal_name', $business->legal_name) }}" class="form-control" /></div>
    <div class="col-md-6 mb-3"><label class="form-label">Tagline</label><input name="tagline" value="{{ old('tagline', $business->tagline) }}" class="form-control" /></div>
  </div>
  <div class="mb-3"><label class="form-label">Description</label><textarea name="description" rows="4" class="form-control">{{ old('description', $business->description) }}</textarea></div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Primary Category</label><input name="primary_category" value="{{ old('primary_category', $business->primary_category) }}" class="form-control" /></div>
    <div class="col-md-4 mb-3"><label class="form-label">Schema Types</label><input name="schema_types" value="{{ old('schema_types', is_array($business->schema_types) ? implode(', ', $business->schema_types) : '') }}" class="form-control" /><div class="form-text">Comma separated.</div></div>
    <div class="col-md-4 mb-3"><label class="form-label">Minimum Age</label><input type="number" name="minimum_age" value="{{ old('minimum_age', $business->minimum_age) }}" class="form-control" min="0" max="99" /></div>
  </div>
  <div class="mb-3"><label class="form-label">Adult Retail Notice</label><textarea name="adult_retail_notice" rows="3" class="form-control">{{ old('adult_retail_notice', $business->adult_retail_notice) }}</textarea></div>
  <div class="form-check form-switch mb-3"><input type="hidden" name="is_active" value="0" /><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $business->is_active) ? 'checked' : '' }} /><label class="form-check-label" for="is_active">Active</label></div>
  @include('admin.partials.seo-fields', ['model' => $business])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Business</button><a href="{{ route('admin.businesses.index') }}" class="btn btn-secondary">Cancel</a></div>
