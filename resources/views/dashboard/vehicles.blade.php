@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="btn-group btn-group-sm flex-wrap">
                        @can('vehicles.show')
                            <a href="{{ route('vehicles.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-car mr-1"></i> Vehicle List
                            </a>
                        @endcan
                        @can('vehicles.create')
                            <a href="{{ route('vehicles.create') }}" class="btn btn-outline-success">
                                <i class="fas fa-plus mr-1"></i> Add Vehicle
                            </a>
                        @endcan
                        @can('fuel-records.show')
                            <a href="{{ route('fuel-records.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-gas-pump mr-1"></i> Fuel Records
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-car"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Vehicles</span>
                            <span class="info-box-number">{{ $totalVehicles }}</span>
                            <span class="progress-description">{{ $activeVehicles }} active</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Expired Documents</span>
                            <span class="info-box-number">{{ $expiredDocs }}</span>
                            <span class="progress-description">STNK / PKB / KIR</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Expiring ≤ 30 days</span>
                            <span class="info-box-number">{{ $expiringSoonDocs }}</span>
                            <span class="progress-description">Needs renewal</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-gas-pump"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fuel this month</span>
                            <span class="info-box-number">{{ number_format((float) $fuelThisMonth, 0, ',', '.') }}</span>
                            <span class="progress-description">Total cost</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Critical vehicles (expired / ≤ 30 days)</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>License Plate</th>
                                <th>Code</th>
                                <th>PIC</th>
                                <th>STNK</th>
                                <th>PKB</th>
                                <th>KIR</th>
                                <th>Location</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($criticalVehicles as $v)
                                <tr>
                                    <td>{{ $v->license_plate }}</td>
                                    <td>{{ $v->kode }}</td>
                                    <td>{{ $v->pic ?: '—' }}</td>
                                    <td>{!! \App\Models\Vehicle::formatExpiryCell($v->documentExpiry('stnk'), $v->daysRemainingFor('stnk')) !!}</td>
                                    <td>{!! \App\Models\Vehicle::formatExpiryCell($v->documentExpiry('pkb'), $v->daysRemainingFor('pkb')) !!}</td>
                                    <td>{!! \App\Models\Vehicle::formatExpiryCell($v->documentExpiry('kir'), $v->daysRemainingFor('kir')) !!}</td>
                                    <td>{{ $v->lokasi ?: '—' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('vehicles.show', $v) }}" class="btn btn-xs btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No critical documents at this time.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
