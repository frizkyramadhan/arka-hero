@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                            <div class="card-tools">
                                @can('personal.official-travel.create-own')
                                    <a href="{{ route('officialtravels.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> New Official Travel
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Travel Date</th>
                                        <th>Destination</th>
                                        <th>Purpose</th>
                                        <th>Traveler</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($travels as $travel)
                                        <tr>
                                            <td>{{ date('d M Y', strtotime($travel->official_travel_date)) }}</td>
                                            <td>{{ $travel->destination }}</td>
                                            <td>{{ Str::limit($travel->purpose, 50) }}</td>
                                            <td>{{ $travel->traveler->employee->fullname ?? 'N/A' }}</td>
                                            <td>
                                                @if ($travel->traveler_id === Auth::user()->administration_id)
                                                    <span class="badge badge-primary">Main Traveler</span>
                                                @else
                                                    <span class="badge badge-info">Follower</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($travel->status)
                                                    @case('draft')
                                                        <span class="badge badge-secondary">Draft</span>
                                                    @break

                                                    @case('submitted')
                                                        <span class="badge badge-info">Submitted</span>
                                                    @break

                                                    @case('approved')
                                                        <span class="badge badge-success">Approved</span>
                                                    @break

                                                    @case('rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @break

                                                    @case('closed')
                                                        <span class="badge badge-dark">Closed</span>
                                                    @break

                                                    @default
                                                        <span class="badge badge-secondary">{{ ucfirst($travel->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <a href="{{ route('officialtravels.show', $travel->id) }}"
                                                    class="btn btn-sm btn-info mr-1">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if ($travel->status === 'draft')
                                                    <a href="{{ route('officialtravels.edit', $travel->id) }}"
                                                        class="btn btn-sm btn-warning mr-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No official travels found</td>
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
