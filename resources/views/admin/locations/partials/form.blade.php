<div class="card-body">
  <h5>Location Information</h5>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Business <span class="text-danger">*</span></label><select name="business_id" class="form-select @error('business_id') is-invalid @enderror" required><option value="">Select Business</option>@foreach($businesses as $business)<option value="{{ $business->id }}" @selected(old('business_id', $location->business_id) == $business->id)>{{ $business->name }}</option>@endforeach</select>@error('business_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Location Name <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $location->name) }}" class="form-control @error('name') is-invalid @enderror" required />@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Slug <span class="text-danger">*</span></label><input name="slug" value="{{ old('slug', $location->slug) }}" class="form-control @error('slug') is-invalid @enderror" required />@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone', $location->phone) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Secondary Phone</label><input name="secondary_phone" value="{{ old('secondary_phone', $location->secondary_phone) }}" class="form-control" /></div>
    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $location->email) }}" class="form-control @error('email') is-invalid @enderror" />@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Address Line 1 <span class="text-danger">*</span></label><input name="address_line_1" value="{{ old('address_line_1', $location->address_line_1) }}" class="form-control @error('address_line_1') is-invalid @enderror" required />@error('address_line_1')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6 mb-3"><label class="form-label">Address Line 2</label><input name="address_line_2" value="{{ old('address_line_2', $location->address_line_2) }}" class="form-control" /></div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">City <span class="text-danger">*</span></label><input name="city" value="{{ old('city', $location->city) }}" class="form-control @error('city') is-invalid @enderror" required />@error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">State <span class="text-danger">*</span></label><input name="state" value="{{ old('state', $location->state) }}" class="form-control @error('state') is-invalid @enderror" required />@error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Postal Code <span class="text-danger">*</span></label><input name="postal_code" value="{{ old('postal_code', $location->postal_code) }}" class="form-control @error('postal_code') is-invalid @enderror" required />@error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Country Code</label><input name="country_code" value="{{ old('country_code', $location->country_code ?: 'US') }}" class="form-control" maxlength="2" required /></div>
  </div>
  <div class="row">
    <div class="col-md-3 mb-3"><label class="form-label">Latitude</label><input name="latitude" value="{{ old('latitude', $location->latitude) }}" class="form-control @error('latitude') is-invalid @enderror" />@error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Longitude</label><input name="longitude" value="{{ old('longitude', $location->longitude) }}" class="form-control @error('longitude') is-invalid @enderror" />@error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-3 mb-3"><label class="form-label">Timezone</label><input name="timezone" value="{{ old('timezone', $location->timezone) }}" class="form-control" /></div>
    <div class="col-md-3 mb-3"><label class="form-label">Price Range</label><input name="price_range" value="{{ old('price_range', $location->price_range) }}" class="form-control" /></div>
  </div>
  <div class="mb-3"><label class="form-label">Google Place ID</label><input name="google_place_id" value="{{ old('google_place_id', $location->google_place_id) }}" class="form-control" /></div>
  <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Google Business URL</label><textarea name="google_business_url" rows="2" class="form-control @error('google_business_url') is-invalid @enderror">{{ old('google_business_url', $location->google_business_url) }}</textarea>@error('google_business_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Google Maps URL</label><textarea name="google_maps_url" rows="2" class="form-control @error('google_maps_url') is-invalid @enderror">{{ old('google_maps_url', $location->google_maps_url) }}</textarea>@error('google_maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4 mb-3"><label class="form-label">Directions URL</label><textarea name="directions_url" rows="2" class="form-control @error('directions_url') is-invalid @enderror">{{ old('directions_url', $location->directions_url) }}</textarea>@error('directions_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
  </div>
  <div class="d-flex gap-4 mb-3">
    <div class="form-check form-switch"><input type="hidden" name="is_primary" value="0" /><input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary" {{ old('is_primary', $location->is_primary) ? 'checked' : '' }} /><label class="form-check-label" for="is_primary">Primary Location</label></div>
    <div class="form-check form-switch"><input type="hidden" name="is_active" value="0" /><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_location" {{ old('is_active', $location->is_active) ? 'checked' : '' }} /><label class="form-check-label" for="is_active_location">Active</label></div>
  </div>
  @include('admin.partials.seo-fields', ['model' => $location])
</div>
<div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save Location</button><a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">Cancel</a></div>
