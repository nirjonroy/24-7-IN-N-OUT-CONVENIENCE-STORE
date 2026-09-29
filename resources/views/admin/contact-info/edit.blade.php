@extends('admin.layouts.app')

@section('title', 'Contact Info')

@section('content')
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Contact Info</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Contact Info</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <div class="card card-primary card-outline mb-4">
              <div class="card-header">
                <div class="card-title">Contact Page Content</div>
              </div>

              <form action="{{ route('admin.contact-info.update') }}" method="POST">
                @csrf
                <div class="card-body">
                  @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                  @endif

                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="eyebrow" class="form-label">Eyebrow</label>
                      <input type="text" name="eyebrow" id="eyebrow" value="{{ old('eyebrow', $contactInfo?->eyebrow) }}" class="form-control @error('eyebrow') is-invalid @enderror" />
                      @error('eyebrow')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 mb-3">
                      <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                      <input type="text" name="title" id="title" value="{{ old('title', $contactInfo?->title) }}" class="form-control @error('title') is-invalid @enderror" required />
                      @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $contactInfo?->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <hr />
                  <h5>Store Address</h5>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="address_title" class="form-label">Address Title</label>
                      <input type="text" name="address_title" id="address_title" value="{{ old('address_title', $contactInfo?->address_title) }}" class="form-control @error('address_title') is-invalid @enderror" />
                      @error('address_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 mb-3">
                      <label for="address" class="form-label">Address</label>
                      <textarea name="address" id="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $contactInfo?->address) }}</textarea>
                      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="google_map_text" class="form-label">Google Map Text</label>
                      <input type="text" name="google_map_text" id="google_map_text" value="{{ old('google_map_text', $contactInfo?->google_map_text) }}" class="form-control @error('google_map_text') is-invalid @enderror" />
                      @error('google_map_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 mb-3">
                      <label for="google_map_url" class="form-label">Google Map URL</label>
                      <textarea name="google_map_url" id="google_map_url" rows="2" class="form-control @error('google_map_url') is-invalid @enderror">{{ old('google_map_url', $contactInfo?->google_map_url) }}</textarea>
                      @error('google_map_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <hr />
                  <h5>Business Details</h5>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="business_details_title" class="form-label">Business Details Title</label>
                      <input type="text" name="business_details_title" id="business_details_title" value="{{ old('business_details_title', $contactInfo?->business_details_title) }}" class="form-control @error('business_details_title') is-invalid @enderror" />
                      @error('business_details_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="business_profile_button_text" class="form-label">Business Profile Button Text</label>
                      <input type="text" name="business_profile_button_text" id="business_profile_button_text" value="{{ old('business_profile_button_text', $contactInfo?->business_profile_button_text) }}" class="form-control @error('business_profile_button_text') is-invalid @enderror" />
                      @error('business_profile_button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="business_details_description" class="form-label">Business Details Description</label>
                    <textarea name="business_details_description" id="business_details_description" rows="3" class="form-control @error('business_details_description') is-invalid @enderror">{{ old('business_details_description', $contactInfo?->business_details_description) }}</textarea>
                    @error('business_details_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="mb-3">
                    <label for="business_profile_url" class="form-label">Business Profile URL</label>
                    <textarea name="business_profile_url" id="business_profile_url" rows="2" class="form-control @error('business_profile_url') is-invalid @enderror">{{ old('business_profile_url', $contactInfo?->business_profile_url) }}</textarea>
                    @error('business_profile_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="mb-3">
                    <label for="map_embed_url" class="form-label">Map Embed URL</label>
                    <textarea name="map_embed_url" id="map_embed_url" rows="3" class="form-control @error('map_embed_url') is-invalid @enderror">{{ old('map_embed_url', $contactInfo?->map_embed_url) }}</textarea>
                    @error('map_embed_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <hr />
                  <h5>Contact Form Text</h5>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="form_eyebrow" class="form-label">Form Eyebrow</label>
                      <input type="text" name="form_eyebrow" id="form_eyebrow" value="{{ old('form_eyebrow', $contactInfo?->form_eyebrow) }}" class="form-control @error('form_eyebrow') is-invalid @enderror" />
                      @error('form_eyebrow')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 mb-3">
                      <label for="form_title" class="form-label">Form Title</label>
                      <input type="text" name="form_title" id="form_title" value="{{ old('form_title', $contactInfo?->form_title) }}" class="form-control @error('form_title') is-invalid @enderror" />
                      @error('form_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="form_description" class="form-label">Form Description</label>
                    <textarea name="form_description" id="form_description" rows="3" class="form-control @error('form_description') is-invalid @enderror">{{ old('form_description', $contactInfo?->form_description) }}</textarea>
                    @error('form_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <div class="form-check form-switch">
                    <input type="hidden" name="status" value="0" />
                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $contactInfo?->status ?? true) ? 'checked' : '' }} />
                    <label class="form-check-label" for="status">Active</label>
                  </div>
                </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Save Contact Info</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
@endsection
