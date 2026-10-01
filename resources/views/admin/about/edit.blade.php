@extends('admin.layouts.app')

@section('title', 'About')

@section('content')
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">About</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">About</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <div class="card card-primary card-outline mb-4">
              <div class="card-header">
                <div class="card-title">About Page Content</div>
              </div>

              <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                  @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                  @endif

                  <h5>Main About Section</h5>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="eyebrow" class="form-label">Eyebrow</label>
                      <input type="text" name="eyebrow" id="eyebrow" value="{{ old('eyebrow', $about?->eyebrow) }}" class="form-control @error('eyebrow') is-invalid @enderror" />
                      @error('eyebrow')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8 mb-3">
                      <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                      <input type="text" name="title" id="title" value="{{ old('title', $about?->title) }}" class="form-control @error('title') is-invalid @enderror" required />
                      @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="description_one" class="form-label">Description One</label>
                      <textarea name="description_one" id="description_one" rows="4" class="form-control @error('description_one') is-invalid @enderror">{{ old('description_one', $about?->description_one) }}</textarea>
                      @error('description_one')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="description_two" class="form-label">Description Two</label>
                      <textarea name="description_two" id="description_two" rows="4" class="form-control @error('description_two') is-invalid @enderror">{{ old('description_two', $about?->description_two) }}</textarea>
                      @error('description_two')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <hr />
                  <h5>Image & CTA</h5>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="image" class="form-label">Image</label>
                      <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" />
                      @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                      @if ($about?->image)
                        <img src="{{ asset('storage/'.$about->image) }}" alt="{{ $about->image_alt ?: $about->title }}" class="img-thumbnail mt-2" style="max-width: 180px;" />
                      @endif
                    </div>

                    <div class="col-md-4 mb-3">
                      <label for="image_alt" class="form-label">Image Alt Text</label>
                      <input type="text" name="image_alt" id="image_alt" value="{{ old('image_alt', $about?->image_alt) }}" class="form-control @error('image_alt') is-invalid @enderror" />
                      @error('image_alt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                      <label class="form-label d-block">Status</label>
                      <div class="form-check form-switch">
                        <input type="hidden" name="status" value="0" />
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $about?->status ?? true) ? 'checked' : '' }} />
                        <label class="form-check-label" for="status">Active</label>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="button_text" class="form-label">Button Text</label>
                      <input type="text" name="button_text" id="button_text" value="{{ old('button_text', $about?->button_text) }}" class="form-control @error('button_text') is-invalid @enderror" />
                      @error('button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                      <label for="button_url" class="form-label">Button URL</label>
                      <input type="text" name="button_url" id="button_url" value="{{ old('button_url', $about?->button_url) }}" class="form-control @error('button_url') is-invalid @enderror" />
                      @error('button_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <hr />
                  <h5>Business Identity Section</h5>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label for="identity_eyebrow" class="form-label">Identity Eyebrow</label>
                      <input type="text" name="identity_eyebrow" id="identity_eyebrow" value="{{ old('identity_eyebrow', $about?->identity_eyebrow) }}" class="form-control @error('identity_eyebrow') is-invalid @enderror" />
                      @error('identity_eyebrow')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8 mb-3">
                      <label for="identity_title" class="form-label">Identity Title</label>
                      <input type="text" name="identity_title" id="identity_title" value="{{ old('identity_title', $about?->identity_title) }}" class="form-control @error('identity_title') is-invalid @enderror" />
                      @error('identity_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="identity_description" class="form-label">Identity Description</label>
                    <textarea name="identity_description" id="identity_description" rows="3" class="form-control @error('identity_description') is-invalid @enderror">{{ old('identity_description', $about?->identity_description) }}</textarea>
                    @error('identity_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>

                  <hr />
                  <h5>Identity Cards</h5>
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="category_one_label" class="form-label">Card 1 Label</label>
                      <input type="text" name="category_one_label" id="category_one_label" value="{{ old('category_one_label', $about?->category_one_label) }}" class="form-control @error('category_one_label') is-invalid @enderror" />
                      @error('category_one_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="category_one_title" class="form-label">Card 1 Title</label>
                      <input type="text" name="category_one_title" id="category_one_title" value="{{ old('category_one_title', $about?->category_one_title) }}" class="form-control @error('category_one_title') is-invalid @enderror" />
                      @error('category_one_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="category_two_label" class="form-label">Card 2 Label</label>
                      <input type="text" name="category_two_label" id="category_two_label" value="{{ old('category_two_label', $about?->category_two_label) }}" class="form-control @error('category_two_label') is-invalid @enderror" />
                      @error('category_two_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="category_two_title" class="form-label">Card 2 Title</label>
                      <input type="text" name="category_two_title" id="category_two_title" value="{{ old('category_two_title', $about?->category_two_title) }}" class="form-control @error('category_two_title') is-invalid @enderror" />
                      @error('category_two_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="category_three_label" class="form-label">Card 3 Label</label>
                      <input type="text" name="category_three_label" id="category_three_label" value="{{ old('category_three_label', $about?->category_three_label) }}" class="form-control @error('category_three_label') is-invalid @enderror" />
                      @error('category_three_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="category_three_title" class="form-label">Card 3 Title</label>
                      <input type="text" name="category_three_title" id="category_three_title" value="{{ old('category_three_title', $about?->category_three_title) }}" class="form-control @error('category_three_title') is-invalid @enderror" />
                      @error('category_three_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="category_four_label" class="form-label">Card 4 Label</label>
                      <input type="text" name="category_four_label" id="category_four_label" value="{{ old('category_four_label', $about?->category_four_label) }}" class="form-control @error('category_four_label') is-invalid @enderror" />
                      @error('category_four_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="category_four_title" class="form-label">Card 4 Title</label>
                      <input type="text" name="category_four_title" id="category_four_title" value="{{ old('category_four_title', $about?->category_four_title) }}" class="form-control @error('category_four_title') is-invalid @enderror" />
                      @error('category_four_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                  </div>
                </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Save About Content</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
@endsection
