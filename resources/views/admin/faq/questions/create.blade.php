@extends('admin.layouts.app')
@section('title', 'Add FAQ')
@section('content')
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Add FAQ</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('admin.faq.questions.index') }}">FAQs</a></li><li class="breadcrumb-item active">Add</li></ol></div></div></div></div>
<div class="app-content"><div class="container-fluid"><form method="POST" action="{{ route('admin.faq.questions.store') }}" class="card">@csrf @include('admin.faq.questions.partials.form')</form></div></div></main>
@endsection
