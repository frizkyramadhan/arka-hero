@extends('layouts.main')

@section('title', $title)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    #form_number_preview.alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
        font-weight: 600;
    }

    #form_number_preview.alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    #form_number_preview::placeholder {
        color: #856404;
        opacity: 0.75;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $subtitle }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vehicle-assignments.index') }}">{{ $title }}</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        @include('vehicle-assignments._form')
    </div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
@include('vehicle-assignments.partials.foa-form-scripts')
@endsection
