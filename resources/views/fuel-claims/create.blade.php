@extends('layouts.main')

@section('title', $title)

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
                    <li class="breadcrumb-item"><a href="{{ route('fuel-claims.index') }}">Fuel Claims</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('fuel-claims.store') }}" method="POST">
            @csrf
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Select verified receipts</h3>
                </div>
                <div class="card-body">
                    @if ($verified->isEmpty())
                        <div class="alert alert-info mb-0">No verified unclaimed fuel records available.</div>
                    @else
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Period from</label>
                                    <input type="date" name="period_from" class="form-control"
                                        value="{{ old('period_from') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Period to</label>
                                    <input type="date" name="period_to" class="form-control"
                                        value="{{ old('period_to') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes</label>
                                    <input type="text" name="notes" class="form-control"
                                        value="{{ old('notes') }}">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="align-middle text-center" width="40">
                                            <input type="checkbox" id="check-all">
                                        </th>
                                        <th class="align-middle">Date</th>
                                        <th class="align-middle">Vehicle</th>
                                        <th class="align-middle">Type</th>
                                        <th class="align-middle">Qty</th>
                                        <th class="align-middle">Total</th>
                                        <th class="align-middle">Station</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($verified as $r)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="fuel_record_ids[]" value="{{ $r->id }}"
                                                    class="row-check"
                                                    @checked(collect(old('fuel_record_ids', []))->contains($r->id))>
                                            </td>
                                            <td>{{ optional($r->fuel_date)->format('Y-m-d') }}</td>
                                            <td>{{ $r->vehicle?->kode }} — {{ $r->vehicle?->license_plate }}</td>
                                            <td>{{ $r->fuel_type }}</td>
                                            <td>{{ number_format((float) $r->quantity, 2) }}</td>
                                            <td>{{ number_format((float) $r->total_cost, 0, ',', '.') }}</td>
                                            <td>{{ $r->fuel_station ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('fuel-claims.index') }}" class="btn btn-default">Cancel</a>
                    @if ($verified->isNotEmpty())
                        <button type="submit" class="btn btn-primary float-right">
                            <i class="fas fa-save"></i> Create claim
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@section('scripts')
<script>
    $('#check-all').on('change', function() {
        $('.row-check').prop('checked', this.checked);
    });
</script>
@endsection
