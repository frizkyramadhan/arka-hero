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
            <div id="loading-entitlements" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Loading leave entitlements...</p>
            </div>

            <div id="error-entitlements" class="alert alert-danger" style="display: none;">
                Failed to load leave entitlements data.
            </div>

            <div class="row" id="entitlements-container" style="display: none;">
                <!-- Entitlements will be populated here via AJAX -->
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadEntitlements();
});

function loadEntitlements() {
    $('#loading-entitlements').show();
    $('#entitlements-container').hide();
    $('#error-entitlements').hide();

    $.ajax({
        url: '{{ route("api.personal.leave.entitlements") }}',
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            $('#loading-entitlements').hide();
            populateEntitlements(response.data);
            $('#entitlements-container').show();
        },
        error: function(xhr, status, error) {
            $('#loading-entitlements').hide();
            $('#error-entitlements').show();
            console.error('Entitlements API Error:', error);
        }
    });
}

function populateEntitlements(data) {
    let container = $('#entitlements-container');
    container.empty();

    if (data.length === 0) {
        container.append(`
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h4>No Leave Entitlements Found</h4>
                        <p class="text-muted">You don't have any active leave entitlements at the moment.</p>
                    </div>
                </div>
            </div>
        `);
        return;
    }

    data.forEach(function(entitlement) {
        let progressWidth = entitlement.entitled > 0 ? (entitlement.remaining / entitlement.entitled) * 100 : 0;
        let alertClass = '';
        let alertHtml = '';

        if (entitlement.is_expired) {
            alertClass = 'border-danger';
            alertHtml = `<div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                This entitlement has expired on ${entitlement.period_end}
            </div>`;
        } else if (entitlement.expires_soon) {
            alertClass = 'border-warning';
            alertHtml = `<div class="alert alert-info mt-3">
                <i class="fas fa-clock"></i>
                This entitlement will expire on ${entitlement.period_end}
            </div>`;
        }

        let cardHtml = `
            <div class="col-md-6 mb-4">
                <div class="card ${alertClass}">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt text-primary mr-2"></i>
                            ${entitlement.leave_type}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="description-block">
                                    <h5 class="description-header text-primary">${entitlement.entitled}</h5>
                                    <span class="description-text">ENTITLED DAYS</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="description-block">
                                    <h5 class="description-header text-success">${entitlement.remaining}</h5>
                                    <span class="description-text">REMAINING DAYS</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: ${progressWidth}%">
                                        <span class="text-white font-weight-bold">
                                            ${entitlement.remaining} / ${entitlement.entitled} days remaining
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-sm-6">
                                <strong>Used:</strong> ${entitlement.used} days
                            </div>
                            <div class="col-sm-6">
                                <strong>Period:</strong><br>
                                <small>
                                    ${entitlement.period_start} - ${entitlement.period_end}
                                </small>
                            </div>
                        </div>
                        ${alertHtml}
                    </div>
                </div>
            </div>
        `;
        container.append(cardHtml);
    });
}

</script>
@endsection
