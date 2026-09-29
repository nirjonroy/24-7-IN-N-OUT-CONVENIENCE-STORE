@extends('admin.layouts.app')

@section('title', 'Contacts')

@section('content')
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Contacts</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Contacts</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
            <div class="card mb-4">
              <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">Contact Messages</h3>
                <form method="GET" class="ms-auto">
                  <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                      <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                  </select>
                </form>
              </div>

              <div class="card-body">
                @if (session('success'))
                  <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                  <table class="table table-bordered table-striped align-middle">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Topic</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th style="width: 170px;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($contacts as $contact)
                        <tr>
                          <td>{{ $contact->name }}</td>
                          <td><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                          <td>{{ $contact->topic ?: '-' }}</td>
                          <td><span class="badge text-bg-{{ $contact->status === 'new' ? 'primary' : ($contact->status === 'spam' ? 'danger' : 'secondary') }}">{{ ucfirst($contact->status) }}</span></td>
                          <td>{{ $contact->created_at->format('M d, Y h:i A') }}</td>
                          <td>
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="btn btn-info btn-sm">
                              <i class="bi bi-eye"></i>
                            </a>
                            <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this contact message?')">
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
                          <td colspan="6" class="text-center text-secondary">No contact messages found.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                {{ $contacts->links() }}
              </div>
            </div>
          </div>
        </div>
      </main>
@endsection
