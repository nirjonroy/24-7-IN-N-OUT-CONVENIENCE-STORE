@extends('admin.layouts.app')
@section('title', 'Edit Repair Service')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Edit Repair Service</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.phone-repair.services.index') }}">Repair Services</a></li><li class="breadcrumb-item active">{{ $service->title }}</li></ol></div></div></div></div><div class="app-content"><div class="container-fluid"><form method="POST" action="{{ route('admin.phone-repair.services.update', $service) }}">@csrf @method('PUT')<div class="card mb-4"><div class="card-header"><h3 class="card-title">Update Service</h3></div>@include('admin.phone-repair.services.partials.form')</div></form></div></div></main>
@endsection
