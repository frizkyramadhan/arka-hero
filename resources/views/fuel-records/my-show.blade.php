@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">{{ $subtitle }}</h1></div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('fuel-records.my-requests') }}" class="btn btn-secondary btn-sm">Back</a>
                    @if ($fuelRecord->isEditableByDriver())
                        @can('personal.fuel.edit-own')
                            <a href="{{ route('fuel-records.my-requests.edit', $fuelRecord) }}" class="btn btn-primary btn-sm">Edit</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid" style="max-width: 640px">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr><th width="40%">Status</th><td>{{ ucfirst($fuelRecord->status) }}</td></tr>
                        <tr><th>Vehicle</th><td>{{ $fuelRecord->vehicle?->kode }} — {{ $fuelRecord->vehicle?->license_plate }}</td></tr>
                        <tr><th>Date</th><td>{{ optional($fuelRecord->fuel_date)->format('d F Y') }}</td></tr>
                        <tr><th>Odometer</th><td>{{ number_format((int)$fuelRecord->odometer) }} km</td></tr>
                        <tr><th>Fuel</th><td>{{ $fuelRecord->fuel_type }}</td></tr>
                        <tr><th>Qty</th><td>{{ number_format((float)$fuelRecord->quantity, 2) }} L</td></tr>
                        <tr><th>Price / L</th><td>{{ number_format((float)$fuelRecord->price_per_liter, 0, ',', '.') }}</td></tr>
                        <tr><th>Total</th><td>{{ number_format((float)$fuelRecord->total_cost, 0, ',', '.') }}</td></tr>
                        <tr><th>Station</th><td>{{ $fuelRecord->fuel_station ?: '—' }}</td></tr>
                        <tr><th>Receipt no.</th><td>{{ $fuelRecord->receipt_number ?: '—' }}</td></tr>
                        @if ($fuelRecord->verification_notes)
                            <tr><th>Verification notes</th><td>{{ $fuelRecord->verification_notes }}</td></tr>
                        @endif
                    </table>
                </div>
                @if ($fuelRecord->receipt_image)
                    <div class="card-footer">
                        <a href="{{ route('fuel-records.receipt', $fuelRecord) }}" target="_blank" class="btn btn-outline-info btn-block">
                            <i class="fas fa-image"></i> View receipt
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
