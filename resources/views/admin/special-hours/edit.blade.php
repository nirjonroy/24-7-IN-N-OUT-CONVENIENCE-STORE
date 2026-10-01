@extends('admin.layouts.app')
@section('title', 'Edit Special Hours')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Edit Special Hours</h3></div></div></div></div><div class="app-content"><div class="container-fluid"><div class="card card-primary card-outline mb-4"><form action="{{ route('admin.locations.special-hours.update', [$location, $specialHour]) }}" method="POST">@csrf @method('PUT') @include('admin.special-hours.partials.form')</form></div></div></div></main>
@endsection
