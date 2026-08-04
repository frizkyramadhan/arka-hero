@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
                        <li class="breadcrumb-item"><a href="{{ route('fuel-records.index') }}">Fuel Records</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('fuel-records.update', $fuelRecord) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Fuel Record</h3>
                    </div>
                    <div class="card-body">
                        @include('fuel-records._form', ['fuelRecord' => $fuelRecord])
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('fuel-records.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });
            function recalc() {
                var q = parseFloat($('#quantity').val()) || 0;
                var p = parseFloat($('#price_per_liter').val()) || 0;
                var t = q * p;
                $('#total_preview').val(t ? t.toLocaleString('id-ID') : '');
            }
            $('#quantity, #price_per_liter').on('input', recalc);
        });
    </script>
@endsection
