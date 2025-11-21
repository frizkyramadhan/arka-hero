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
                            @can('personal.leave.create-own')
                            <a href="{{ route('leave.requests.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New Leave Request
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="loading-table" class="text-center" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Loading leave requests...</p>
                        </div>
                        <div id="error-table" class="alert alert-danger" style="display: none;">
                            Failed to load leave requests data.
                        </div>
                        <table id="leave-requests-table" class="table table-bordered table-striped" style="display: none;">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadLeaveRequests();
});

function loadLeaveRequests() {
    $('#loading-table').show();
    $('#leave-requests-table').hide();
    $('#error-table').hide();

    $.ajax({
        url: '{{ route("api.personal.leave.requests") }}',
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            $('#loading-table').hide();
            populateLeaveRequestsTable(response.data);
            $('#leave-requests-table').show();
        },
        error: function(xhr, status, error) {
            $('#loading-table').hide();
            $('#error-table').show();
            console.error('Leave Requests API Error:', error);
        }
    });
}

function populateLeaveRequestsTable(data) {
    let tbody = $('#leave-requests-table tbody');
    tbody.empty();

    if (data.length === 0) {
        tbody.append('<tr><td colspan="6" class="text-center text-muted">No leave requests found</td></tr>');
        return;
    }

    data.forEach(function(request) {
        let badgeClass = 'badge-secondary';
        if (request.status === 'approved') badgeClass = 'badge-success';
        else if (request.status === 'pending') badgeClass = 'badge-warning';
        else if (request.status === 'rejected') badgeClass = 'badge-danger';

        let actions = '';
        if (request.actions.view_url) {
            actions += `<a href="${request.actions.view_url}" class="btn btn-sm btn-info mr-1">
                            <i class="fas fa-eye"></i> View
                        </a>`;
        }
        if (request.actions.edit_url) {
            actions += `<a href="${request.actions.edit_url}" class="btn btn-sm btn-warning mr-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>`;
        }

        let row = `
            <tr>
                <td>${request.leave_type}</td>
                <td>${request.leave_period}</td>
                <td>${request.total_days} days</td>
                <td><span class="badge ${badgeClass}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span></td>
                <td>${new Date(request.created_at).toLocaleDateString()}</td>
                <td>${actions}</td>
            </tr>
        `;
        tbody.append(row);
    });

    // Initialize DataTable for sorting and searching
    if ($.fn.DataTable.isDataTable('#leave-requests-table')) {
        $('#leave-requests-table').DataTable().destroy();
    }

    $('#leave-requests-table').DataTable({
        pageLength: 25,
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: [3, 5] }
        ]
    });
}

</script>
@endsection
