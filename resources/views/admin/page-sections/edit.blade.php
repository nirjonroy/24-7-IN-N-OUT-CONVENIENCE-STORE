@extends('admin.layouts.app')
@section('title', 'Edit Section')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Edit Section</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Pages</a></li><li class="breadcrumb-item"><a href="{{ route('admin.pages.sections.index', $page) }}">{{ $page->name }} Sections</a></li><li class="breadcrumb-item active">{{ $section->title ?: $section->section_key }}</li></ol></div></div></div></div>
<div class="app-content"><div class="container-fluid"><form action="{{ route('admin.pages.sections.update', [$page, $section]) }}" method="POST">@csrf @method('PUT')<div class="card mb-4"><div class="card-header"><h3 class="card-title">Update Section</h3></div>@include('admin.page-sections.partials.form')</div></form></div></div></main>
@endsection
