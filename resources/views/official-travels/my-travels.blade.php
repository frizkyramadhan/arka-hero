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
                        <div id="loading-table" class="text-center" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Loading official travels...</p>
                        </div>
                        <div id="error-table" class="alert alert-danger" style="display: none;">
                            Failed to load official travels data.
                        </div>
                        <table id="official-travels-table" class="table table-bordered table-striped" style="display: none;">
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
    loadOfficialTravels();
});

function loadOfficialTravels() {
    $('#loading-table').show();
    $('#official-travels-table').hide();
    $('#error-table').hide();

    $.ajax({
        url: '{{ route("api.personal.official-travels") }}',
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            $('#loading-table').hide();
            populateTravelsTable(response.data);
            $('#official-travels-table').show();
        },
        error: function(xhr, status, error) {
            $('#loading-table').hide();
            $('#error-table').show();
            console.error('Official Travels API Error:', error);
        }
    });
}

function populateTravelsTable(data) {
    let tbody = $('#official-travels-table tbody');
    tbody.empty();

    if (data.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center text-muted">No official travels found</td></tr>');
        return;
    }

    data.forEach(function(travel) {
        let badgeClass = 'badge-secondary';
        if (travel.status === 'approved') badgeClass = 'badge-success';
        else if (travel.status === 'submitted') badgeClass = 'badge-info';
        else if (travel.status === 'rejected') badgeClass = 'badge-danger';

        let roleBadge = travel.role === 'Main Traveler' ? 'badge-primary' : 'badge-info';

        let actions = '';
        if (travel.actions.view_url) {
            actions += `<a href="${travel.actions.view_url}" class="btn btn-sm btn-info mr-1">
                            <i class="fas fa-eye"></i> View
                        </a>`;
        }
        if (travel.actions.edit_url) {
            actions += `<a href="${travel.actions.edit_url}" class="btn btn-sm btn-warning mr-1">
                            <i class="fas fa-edit"></i> Edit
                        </a>`;
        }

        let row = `
            <tr>
                <td>${travel.travel_date}</td>
                <td>${travel.destination}</td>
                <td>${travel.purpose}</td>
                <td>${travel.traveler_name}</td>
                <td><span class="badge ${roleBadge}">${travel.role}</span></td>
                <td><span class="badge ${badgeClass}">${travel.status.charAt(0).toUpperCase() + travel.status.slice(1)}</span></td>
                <td>${actions}</td>
            </tr>
        `;
        tbody.append(row);
    });

    // Initialize DataTable for sorting and searching
    if ($.fn.DataTable.isDataTable('#official-travels-table')) {
        $('#official-travels-table').DataTable().destroy();
    }

    $('#official-travels-table').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [4, 5, 6] }
        ]
    });
}

</script>
@endsection
