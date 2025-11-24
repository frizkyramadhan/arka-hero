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
                        <li class="breadcrumb-item"><a href="{{ route('leave.requests.my-requests') }}">My Leave Requests</a>
                        </li>
                        <li class="breadcrumb-item active">Entitlements</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                @forelse($entitlements as $entitlement)
                    <div class="col-md-4 mb-3">
                        <div
                            class="card border-left-primary {{ $entitlement['is_expired'] ? 'border-danger' : ($entitlement['expires_soon'] ? 'border-warning' : '') }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $entitlement['leave_type'] }}</h5>
                                <p class="card-text">
                                    <strong>Entitled:</strong> {{ $entitlement['entitled_days'] }} days<br>
                                    <strong>Used:</strong> {{ $entitlement['taken_days'] }} days<br>
                                    <strong>Remaining:</strong> {{ $entitlement['remaining_days'] }} days<br>
                                    <small class="text-muted">Valid until: {{ $entitlement['period_end'] }}</small>
                                </p>
                                <div class="progress">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $entitlement['entitled_days'] > 0 ? ($entitlement['remaining_days'] / $entitlement['entitled_days']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No active leave entitlements found.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
