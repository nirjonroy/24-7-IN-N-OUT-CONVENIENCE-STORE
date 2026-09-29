@extends('admin.layouts.app')

@section('title', 'Site Info')

@section('content')
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Site Info</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Site Info</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-lg-8">
                <div class="card card-primary card-outline mb-4">
                  <div class="card-header">
                    <div class="card-title">Update Site Information</div>
                  </div>

                  <form action="{{ route('admin.siteinfo.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                      @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                      @endif

                      <div class="mb-3">
                        <label for="name" class="form-label">Site Name <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          name="name"
                          id="name"
                          value="{{ old('name', $siteinfo?->name) }}"
                          class="form-control @error('name') is-invalid @enderror"
                          required
                        />
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>

                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="email" class="form-label">Email</label>
                          <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email', $siteinfo?->email) }}"
                            class="form-control @error('email') is-invalid @enderror"
                          />
                          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                          <label for="phone" class="form-label">Phone</label>
                          <input
                            type="text"
                            name="phone"
                            id="phone"
                            value="{{ old('phone', $siteinfo?->phone) }}"
                            class="form-control @error('phone') is-invalid @enderror"
                          />
                          @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                      </div>

                      <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea
                          name="address"
                          id="address"
                          rows="4"
                          class="form-control @error('address') is-invalid @enderror"
                        >{{ old('address', $siteinfo?->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      </div>

                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="logo" class="form-label">Logo</label>
                          <input
                            type="file"
                            name="logo"
                            id="logo"
                            class="form-control @error('logo') is-invalid @enderror"
                            accept="image/*"
                          />
                          @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                          <label for="favicon" class="form-label">Favicon</label>
                          <input
                            type="file"
                            name="favicon"
                            id="favicon"
                            class="form-control @error('favicon') is-invalid @enderror"
                            accept="image/*,.ico"
                          />
                          @error('favicon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                      </div>
                    </div>

                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary">Save Site Info</button>
                    </div>
                  </form>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="card mb-4">
                  <div class="card-header">
                    <div class="card-title">Current Media</div>
                  </div>
                  <div class="card-body">
                    <div class="mb-4">
                      <h6 class="mb-2">Logo</h6>
                      @if ($siteinfo?->logo)
                        <img src="{{ asset('storage/'.$siteinfo->logo) }}" alt="Site Logo" class="img-fluid border rounded p-2" />
                      @else
                        <p class="text-secondary mb-0">No logo uploaded.</p>
                      @endif
                    </div>

                    <div>
                      <h6 class="mb-2">Favicon</h6>
                      @if ($siteinfo?->favicon)
                        <img src="{{ asset('storage/'.$siteinfo->favicon) }}" alt="Favicon" class="border rounded p-2" style="max-width: 96px;" />
                      @else
                        <p class="text-secondary mb-0">No favicon uploaded.</p>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
@endsection
