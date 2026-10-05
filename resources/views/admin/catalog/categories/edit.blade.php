@extends('admin.layouts.app')
@section('title', 'Edit Catalog Category')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Edit Catalog Category</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.catalog.categories.index') }}">Catalog Categories</a></li><li class="breadcrumb-item active">{{ $category->name }}</li></ol></div></div></div></div>
<div class="app-content"><div class="container-fluid"><form action="{{ route('admin.catalog.categories.update', $category) }}" method="POST">@csrf @method('PUT')<div class="card mb-4"><div class="card-header"><h3 class="card-title">Update Category</h3></div>@include('admin.catalog.categories.partials.form')</div></form></div></div></main>
@endsection
