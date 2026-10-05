@extends('admin.layouts.app')
@section('title', 'Add Gallery Category')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Add Gallery Category</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.gallery.categories.index') }}">Gallery Categories</a></li><li class="breadcrumb-item active">Add</li></ol></div></div></div></div>
<div class="app-content"><div class="container-fluid"><form method="POST" action="{{ route('admin.gallery.categories.store') }}" class="card">@csrf @include('admin.gallery.categories.partials.form')</form></div></div></main>
@endsection
