                <div class="card-body">
                  <div class="row">
                    <div class="col-md-8 mb-3">
                      <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                      <input type="text" name="title" id="title" value="{{ old('title', $slider->title) }}" class="form-control @error('title') is-invalid @enderror" required />
                      @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                      <label class="form-label d-block">Status</label>
                      <div class="form-check form-switch">
                        <input type="hidden" name="status" value="0" />
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $slider->status) ? 'checked' : '' }} />
                        <label class="form-check-label" for="status">Active</label>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $slider->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="primary_button_text" class="form-label">Primary Button Text</label>
                      <input type="text" name="primary_button_text" id="primary_button_text" value="{{ old('primary_button_text', $slider->primary_button_text) }}" class="form-control @error('primary_button_text') is-invalid @enderror" />
                      @error('primary_button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="primary_button_url" class="form-label">Primary Button URL</label>
                      <input type="text" name="primary_button_url" id="primary_button_url" value="{{ old('primary_button_url', $slider->primary_button_url) }}" class="form-control @error('primary_button_url') is-invalid @enderror" />
                      @error('primary_button_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="secondary_button_text" class="form-label">Secondary Button Text</label>
                      <input type="text" name="secondary_button_text" id="secondary_button_text" value="{{ old('secondary_button_text', $slider->secondary_button_text) }}" class="form-control @error('secondary_button_text') is-invalid @enderror" />
                      @error('secondary_button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="secondary_button_url" class="form-label">Secondary Button URL</label>
                      <input type="text" name="secondary_button_url" id="secondary_button_url" value="{{ old('secondary_button_url', $slider->secondary_button_url) }}" class="form-control @error('secondary_button_url') is-invalid @enderror" />
                      @error('secondary_button_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="image" class="form-label">Image</label>
                      <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" />
                      @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      @if ($slider->image)
                        <img src="{{ asset('storage/'.$slider->image) }}" alt="{{ $slider->image_alt ?: $slider->title }}" class="img-thumbnail mt-2" style="max-width: 180px;" />
                      @endif
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="image_alt" class="form-label">Image Alt Text</label>
                      <input type="text" name="image_alt" id="image_alt" value="{{ old('image_alt', $slider->image_alt) }}" class="form-control @error('image_alt') is-invalid @enderror" />
                      @error('image_alt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="address_title" class="form-label">Address Title</label>
                      <input type="text" name="address_title" id="address_title" value="{{ old('address_title', $slider->address_title) }}" class="form-control @error('address_title') is-invalid @enderror" />
                      @error('address_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="address_subtitle" class="form-label">Address Subtitle</label>
                      <input type="text" name="address_subtitle" id="address_subtitle" value="{{ old('address_subtitle', $slider->address_subtitle) }}" class="form-control @error('address_subtitle') is-invalid @enderror" />
                      @error('address_subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="map_button_text" class="form-label">Map Button Text</label>
                      <input type="text" name="map_button_text" id="map_button_text" value="{{ old('map_button_text', $slider->map_button_text) }}" class="form-control @error('map_button_text') is-invalid @enderror" />
                      @error('map_button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8 mb-3">
                      <label for="map_url" class="form-label">Map URL</label>
                      <textarea name="map_url" id="map_url" rows="2" class="form-control @error('map_url') is-invalid @enderror">{{ old('map_url', $slider->map_url) }}</textarea>
                      @error('map_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>
                </div>

                <div class="card-footer d-flex gap-2">
                  <button type="submit" class="btn btn-primary">Save Slider</button>
                  <a href="{{ route('admin.sliders.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
