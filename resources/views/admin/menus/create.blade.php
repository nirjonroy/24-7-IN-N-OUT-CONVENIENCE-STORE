@extends('admin.layouts.app')
@section('title', 'Add Menu')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Add Menu</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">Menus</a></li><li class="breadcrumb-item active">Add</li></ol></div></div></div></div>
<div class="app-content"><div class="container-fluid"><form method="POST" action="{{ route('admin.menus.store') }}" class="card">@csrf @include('admin.menus.partials.form')</form></div></div></main>
@endsection
