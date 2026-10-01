@extends('admin.layouts.app')
@section('title', 'Add Social Link')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Add Social Link</h3></div></div></div></div><div class="app-content"><div class="container-fluid"><div class="card card-primary card-outline mb-4"><form action="{{ route('admin.businesses.social-links.store', $business) }}" method="POST">@csrf @include('admin.social-links.partials.form')</form></div></div></div></main>
@endsection
