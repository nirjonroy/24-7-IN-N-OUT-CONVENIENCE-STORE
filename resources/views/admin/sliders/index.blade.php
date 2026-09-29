@extends('admin.layouts.app')

@section('title', 'Sliders')

@section('content')
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Sliders</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Sliders</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <div class="card mb-4">
              <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">Slider List</h3>
                <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary btn-sm ms-auto">
                  <i class="bi bi-plus-lg"></i> Add Slider
                </a>
              </div>

              <div class="card-body">
                @if (session('success'))
                  <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                  <table class="table table-bordered table-striped align-middle">
                    <thead>
                      <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Title</th>
                        <th>Primary Button</th>
                        <th>Status</th>
                        <th style="width: 170px;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($sliders as $slider)
                        <tr>
                          <td>
                            @if ($slider->image)
                              <img src="{{ asset('storage/'.$slider->image) }}" alt="{{ $slider->image_alt ?: $slider->title }}" class="img-thumbnail" style="width: 64px; height: 48px; object-fit: cover;" />
                            @else
                              <span class="text-secondary">No image</span>
                            @endif
                          </td>
                          <td>
                            <strong>{{ $slider->title }}</strong>
                            @if ($slider->description)
                              <div class="small text-secondary">{{ Str::limit($slider->description, 80) }}</div>
                            @endif
                          </td>
                          <td>{{ $slider->primary_button_text ?: '-' }}</td>
                          <td>
                            <span class="badge {{ $slider->status ? 'text-bg-success' : 'text-bg-secondary' }}">
                              {{ $slider->status ? 'Active' : 'Inactive' }}
                            </span>
                          </td>
                          <td>
                            <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-warning btn-sm">
                              <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this slider?')">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center text-secondary">No sliders found.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                {{ $sliders->links() }}
              </div>
            </div>
          </div>
        </div>
      </main>
@endsection
