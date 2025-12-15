@extends('layouts.main')

@section('title', $title)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('rosters.index') }}">Roster Management</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-balance-scale mr-2"></i>
                                Roster dengan Adjustment
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('rosters.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to Roster
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filter -->
                            <form method="GET" action="{{ route('rosters.adjustments') }}" class="mb-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Project</label>
                                            <select name="project_id" class="form-control select2" style="width: 100%;">
                                                <option value="">All Projects</option>
                                                @foreach($projects as $project)
                                                    <option value="{{ $project->id }}" 
                                                        {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Year</label>
                                            <select name="year" class="form-control">
                                                @for($y = now()->year; $y >= now()->year - 2; $y--)
                                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                                        {{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Month</label>
                                            <select name="month" class="form-control">
                                                @for($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                                        {{ Carbon\Carbon::create(null, $m)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search mr-1"></i> Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Employee</th>
                                            <th>NIK</th>
                                            <th>Project</th>
                                            <th>Department</th>
                                            <th>Base Work Days</th>
                                            <th>Net Adjustment</th>
                                            <th>Adjusted Work Days</th>
                                            <th>Last Adjustment</th>
                                            <th>Total Adjustments</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rosters as $index => $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="font-weight-bold">
                                                    {{ $item['employee']->fullname ?? 'N/A' }}
                                                </td>
                                                <td>{{ $item['employee']->nik ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ $item['project']->project_code ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>{{ $item['department']->name ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-secondary">
                                                        {{ $item['base_work_days'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($item['net_adjustment'] > 0)
                                                        <span class="badge badge-success">
                                                            +{{ $item['net_adjustment'] }} days
                                                        </span>
                                                    @elseif($item['net_adjustment'] < 0)
                                                        <span class="badge badge-danger">
                                                            {{ $item['net_adjustment'] }} days
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">
                                                        {{ $item['adjusted_work_days'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($item['last_adjustment_date'])
                                                        {{ $item['last_adjustment_date']->format('d M Y H:i') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-warning">
                                                        {{ $item['adjustments']->count() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" 
                                                            onclick="showAdjustmentHistory({{ $item['roster']->id }}, '{{ $item['employee']->fullname ?? 'N/A' }}')"
                                                            title="View History">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                    <a href="{{ route('rosters.index', ['project_id' => $item['project']->id ?? '', 'year' => request('year', now()->year), 'month' => request('month', now()->month)]) }}" 
                                                       class="btn btn-sm btn-primary"
                                                       title="View Roster">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        No rosters with adjustments found.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">
                    <i class="fas fa-history mr-2"></i>
                    Adjustment History for <span id="historyEmployeeName"></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Reason</th>
                            <th>Effective Date</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <tr><td colspan="5" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showAdjustmentHistory(rosterId, employeeName) {
        $('#historyEmployeeName').text(employeeName);
        $('#historyTableBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $('#historyModal').modal('show');
        
        $.ajax({
            url: '{{ route("rosters.balancing-history", ":id") }}'.replace(':id', rosterId),
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(item) {
                        const typeBadge = item.adjustment_type === '+days' 
                            ? '<span class="badge badge-success">+' + item.adjusted_value + ' days</span>'
                            : '<span class="badge badge-danger">-' + item.adjusted_value + ' days</span>';
                        
                        html += '<tr>';
                        html += '<td>' + item.created_at + '</td>';
                        html += '<td>' + typeBadge + '</td>';
                        html += '<td>' + item.adjusted_value + '</td>';
                        html += '<td>' + item.reason + '</td>';
                        html += '<td>' + (item.effective_date || 'N/A') + '</td>';
                        html += '</tr>';
                    });
                    $('#historyTableBody').html(html);
                } else {
                    $('#historyTableBody').html('<tr><td colspan="5" class="text-center">No history found.</td></tr>');
                }
            },
            error: function() {
                $('#historyTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error loading history.</td></tr>');
            }
        });
    }
</script>
@endsection

