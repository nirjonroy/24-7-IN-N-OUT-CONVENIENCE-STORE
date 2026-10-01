@extends('admin.layouts.app')
@section('title', 'Add Location')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Add Location</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.locations.index') }}">Locations</a></li><li class="breadcrumb-item active">Create</li></ol></div></div></div></div><div class="app-content"><div class="container-fluid"><div class="card card-primary card-outline mb-4"><div class="card-header"><div class="card-title">Location Information</div></div><form action="{{ route('admin.locations.store') }}" method="POST">@csrf @include('admin.locations.partials.form')</form></div></div></div></main>
@endsection
