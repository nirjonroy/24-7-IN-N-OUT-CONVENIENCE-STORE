@extends('admin.layouts.app')

@section('title', 'Contact Message')

@section('content')
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Contact Message</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
                  <li class="breadcrumb-item active" aria-current="page">View</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-lg-8">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">Message</h3>
                  </div>
                  <div class="card-body">
                    @if (session('success'))
                      <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <dl class="row mb-0">
                      <dt class="col-sm-3">Name</dt>
                      <dd class="col-sm-9">{{ $contact->name }}</dd>
                      <dt class="col-sm-3">Email</dt>
                      <dd class="col-sm-9"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></dd>
                      <dt class="col-sm-3">Topic</dt>
                      <dd class="col-sm-9">{{ $contact->topic ?: '-' }}</dd>
                      <dt class="col-sm-3">Message</dt>
                      <dd class="col-sm-9">{{ $contact->message }}</dd>
                    </dl>
                  </div>
                </div>
              </div>

              <div class="col-lg-4">
                <div class="card mb-4">
                  <div class="card-header">
                    <h3 class="card-title">Manage</h3>
                  </div>
                  <div class="card-body">
                    <form action="{{ route('admin.contacts.update', $contact) }}" method="POST" class="mb-3">
                      @csrf
                      @method('PUT')
                      <label for="status" class="form-label">Status</label>
                      <select name="status" id="status" class="form-select mb-3">
                        @foreach ($statuses as $status)
                          <option value="{{ $status }}" @selected($contact->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                      </select>
                      <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>

                    <p class="mb-1"><strong>IP:</strong> {{ $contact->ip_address ?: '-' }}</p>
                    <p class="mb-1"><strong>Submitted:</strong> {{ $contact->created_at->format('M d, Y h:i A') }}</p>
                    <p class="mb-1"><strong>Read:</strong> {{ $contact->read_at?->format('M d, Y h:i A') ?: '-' }}</p>
                    <p class="mb-0"><strong>Replied:</strong> {{ $contact->replied_at?->format('M d, Y h:i A') ?: '-' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
@endsection
