@extends('layouts.main')

@section('title', $title)

@section('content')
@php
    $statusClass = [
        'draft' => 'secondary',
        'ready' => 'success',
        'sent' => 'info',
        'realized' => 'primary',
        'cancelled' => 'danger',
    ][$fuelClaim->status] ?? 'secondary';
@endphp
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $fuelClaim->claim_number }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fuel-claims.index') }}">Fuel Claims</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Claim summary</h3>
                        <div class="card-tools">
                            <a href="{{ route('fuel-claims.index') }}" class="btn btn-default btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tr>
                                <th width="40%">Status</th>
                                <td><span class="badge badge-{{ $statusClass }}">{{ ucfirst($fuelClaim->status) }}</span></td>
                            </tr>
                            <tr>
                                <th>Period</th>
                                <td>
                                    {{ optional($fuelClaim->period_from)->format('Y-m-d') }}
                                    →
                                    {{ optional($fuelClaim->period_to)->format('Y-m-d') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Items</th>
                                <td>{{ $fuelClaim->records->count() }}</td>
                            </tr>
                            <tr>
                                <th>Total qty</th>
                                <td>{{ number_format((float) $fuelClaim->total_quantity, 2) }} L</td>
                            </tr>
                            <tr>
                                <th>Total cost</th>
                                <td>Rp {{ number_format((float) $fuelClaim->total_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>External ref</th>
                                <td>{{ $fuelClaim->external_ref ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td>{{ $fuelClaim->notes ?: '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    @if (in_array($fuelClaim->status, ['draft', 'ready'], true))
                        <div class="card-footer">
                            @if ($fuelClaim->status === 'draft')
                                @can('fuel-claims.ready')
                                    <form action="{{ route('fuel-claims.ready', $fuelClaim) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm"
                                            onclick="return confirm('Mark ready for external realization?')">
                                            <i class="fas fa-check"></i> Mark ready
                                        </button>
                                    </form>
                                @endcan
                            @endif
                            @can('fuel-claims.delete')
                                <form action="{{ route('fuel-claims.cancel', $fuelClaim) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Cancel claim and return items to verified?')">
                                        <i class="fas fa-ban"></i> Cancel claim
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Receipts in this claim</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Vehicle</th>
                                    <th class="align-middle">Type</th>
                                    <th class="align-middle">Qty</th>
                                    <th class="align-middle">Total</th>
                                    <th class="align-middle text-center">Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fuelClaim->records as $r)
                                    <tr>
                                        <td>{{ optional($r->fuel_date)->format('Y-m-d') }}</td>
                                        <td>{{ $r->vehicle?->kode }} — {{ $r->vehicle?->license_plate }}</td>
                                        <td>{{ $r->fuel_type }}</td>
                                        <td>{{ number_format((float) $r->quantity, 2) }}</td>
                                        <td>{{ number_format((float) $r->total_cost, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if ($r->receipt_image)
                                                <a href="{{ route('fuel-records.receipt', $r) }}" target="_blank"
                                                    class="btn btn-info btn-sm" title="Receipt">
                                                    <i class="fas fa-image"></i>
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No receipts</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
