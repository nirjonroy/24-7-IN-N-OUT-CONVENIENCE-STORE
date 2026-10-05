@extends('admin.layouts.app')
@section('title', 'Add Section')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Add Section</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li><li class="breadcrumb-item"><a href="{{ route('admin.pages.sections.index', $page) }}">{{ $page->name }} Sections</a></li><li class="breadcrumb-item active">Add Section</li></ol></div></div></div></div>
<div class="app-content"><div class="container-fluid"><form action="{{ route('admin.pages.sections.store', $page) }}" method="POST">@csrf<div class="card mb-4"><div class="card-header"><h3 class="card-title">Section Information</h3></div>@include('admin.page-sections.partials.form')</div></form></div></div></main>
@endsection
