@extends('admin.layouts.app')
@section('title', 'Add Menu Item')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Add Menu Item</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">Menus</a></li><li class="breadcrumb-item"><a href="{{ route('admin.menus.items.index', $menu) }}">{{ $menu->name }} Items</a></li><li class="breadcrumb-item active">Add</li></ol></div></div></div></div>
<div class="app-content"><div class="container-fluid"><form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="card">@csrf @include('admin.menu-items.partials.form')</form></div></div></main>
@endsection
