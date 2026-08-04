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
                <div class="col-sm-6"><h1 class="m-0">Edit fuel log</h1></div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid" style="max-width: 640px">
            <form action="{{ route('fuel-records.my-requests.update', $fuelRecord) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        @if ($fuelRecord->status === 'rejected' && $fuelRecord->verification_notes)
                            <div class="alert alert-danger">{{ $fuelRecord->verification_notes }}</div>
                        @endif
                        <div class="form-group">
                            <label>Replace receipt photo (optional)</label>
                            <input type="file" name="receipt_image" class="form-control-file" accept="image/*" capture="environment">
                        </div>
                        @include('fuel-records._my-form-fields', [
                            'vehicles' => $vehicles,
                            'record' => $fuelRecord,
                        ])
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-block btn-lg">Resubmit</button>
                        <a href="{{ route('fuel-records.my-requests.show', $fuelRecord) }}" class="btn btn-secondary btn-block">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>$('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });</script>
@endsection
