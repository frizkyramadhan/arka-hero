@extends('layouts.main')

@section('title', 'My Flight Request Details')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Flight Request Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">My Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('flight-requests.my-requests') }}">My Flight Requests</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plane mr-2"></i>
                                <strong>Flight Request Information</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Form Number:</dt>
                                <dd class="col-sm-8">{{ $flightRequest->form_number ?? '-' }}</dd>

                                <dt class="col-sm-4">Purpose of Travel:</dt>
                                <dd class="col-sm-8">{{ $flightRequest->purpose_of_travel }}</dd>

                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    @php
                                        $badges = [
                                            'draft' => 'badge-secondary',
                                            'submitted' => 'badge-info',
                                            'approved' => 'badge-success',
                                            'issued' => 'badge-primary',
                                            'completed' => 'badge-dark',
                                            'rejected' => 'badge-danger',
                                            'cancelled' => 'badge-warning'
                                        ];
                                        $statusClass = $badges[$flightRequest->status] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($flightRequest->status) }}</span>
                                </dd>

                                <dt class="col-sm-4">Requested At:</dt>
                                <dd class="col-sm-8">{{ $flightRequest->requested_at ? $flightRequest->requested_at->format('d/m/Y H:i') : '-' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Flight Details -->
                    <div class="card card-info card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-route mr-2"></i>
                                <strong>Flight Details</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Segment</th>
                                        <th>Date</th>
                                        <th>Route</th>
                                        <th>Airline</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($flightRequest->details as $detail)
                                        <tr>
                                            <td>{{ ucfirst($detail->segment_type) }}</td>
                                            <td>{{ $detail->flight_date->format('d/m/Y') }}</td>
                                            <td>{{ $detail->departure_city }} → {{ $detail->arrival_city }}</td>
                                            <td>{{ $detail->airline ?? '-' }}</td>
                                            <td>{{ $detail->flight_time ? \Carbon\Carbon::parse($detail->flight_time)->format('H:i') : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No flight details</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Issuances -->
                    @if($flightRequest->issuances->count() > 0)
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-invoice mr-2"></i>
                                    <strong>Letter of Guarantee (LG)</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                @foreach($flightRequest->issuances as $issuance)
                                    <div class="border p-3 mb-3">
                                        <h5>LG: {{ $issuance->issued_number }}</h5>
                                        <p><strong>Issued Date:</strong> {{ $issuance->issued_date->format('d/m/Y') }}</p>
                                        <p><strong>Business Partner:</strong> {{ $issuance->businessPartner->bp_name ?? '-' }}</p>
                                        <p><strong>Total Tickets:</strong> {{ $issuance->issuanceDetails->count() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
