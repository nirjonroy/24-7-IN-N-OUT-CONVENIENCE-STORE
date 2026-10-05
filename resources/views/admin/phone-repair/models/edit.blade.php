@extends('admin.layouts.app')
@section('title', 'Edit Device Model')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Edit Device Model</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.phone-repair.models.index') }}">Device Models</a></li><li class="breadcrumb-item active">{{ $model->name }}</li></ol></div></div></div></div><div class="app-content"><div class="container-fluid"><form method="POST" action="{{ route('admin.phone-repair.models.update', $model) }}">@csrf @method('PUT')<div class="card mb-4"><div class="card-header"><h3 class="card-title">Update Model</h3></div>@include('admin.phone-repair.models.partials.form')</div></form></div></div></main>
@endsection
