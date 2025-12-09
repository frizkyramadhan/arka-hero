@extends('layouts.main')

@section('title', 'Periodic Leave Request Detail')

@section('content')
    <div class="content-wrapper-custom">
        <div class="leave-request-header">
            <div class="leave-request-header-content">
                <div class="leave-request-project">Periodic Leave Request Batch</div>
                <h1 class="leave-request-number">Batch: {{ $batchInfo['batch_id'] }}</h1>
                <div class="leave-request-date">
                    <i class="far fa-calendar-alt"></i> {{ $batchInfo['created_at']->format('d F Y') }}
                </div>
                <div class="leave-request-status-pill">
                    <span class="badge badge-info">
                        <i class="fas fa-layer-group"></i> {{ $batchInfo['total_requests'] }} Requests
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="leave-request-content">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Batch Information -->
                    <div class="leave-request-card leave-request-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-layer-group"></i> Batch Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Batch ID</div>
                                        <div class="info-value">{{ $batchInfo['batch_id'] }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Total Requests</div>
                                        <div class="info-value">{{ $batchInfo['total_requests'] }} leave requests</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Created At</div>
                                        <div class="info-value">{{ $batchInfo['created_at']->format('d F Y H:i') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Created By</div>
                                        <div class="info-value">{{ $batchInfo['requested_by']->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                @if ($batchInfo['bulk_notes'])
                                    <div class="info-item" style="grid-column: 1 / -1;">
                                        <div class="info-icon" style="background-color: #f39c12;">
                                            <i class="fas fa-sticky-note"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Notes</div>
                                            <div class="info-value">{{ $batchInfo['bulk_notes'] }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Batch Actions -->
                    @if ($statusCounts->get('pending', 0) > 0)
                        <div class="leave-request-card mb-4">
                            <div class="card-head bg-danger text-white">
                                <h2><i class="fas fa-exclamation-triangle"></i> Batch Actions</h2>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">You can cancel all pending requests in this batch at once.</p>
                                <button type="button" class="btn btn-danger btn-block" onclick="cancelBatch()">
                                    <i class="fas fa-ban mr-1"></i> Cancel All Pending Requests
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="leave-request-action-buttons">
                        <a href="{{ route('leave.periodic-requests.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Leave Requests List Grouped by Department -->
            <div class="row">
                <div class="col-12">
                    @php
                        $groupedRequests = $leaveRequests->groupBy(function ($request) {
                            return $request->administration->position->department->department_name ?? 'Unknown';
                        });
                    @endphp

                    @foreach ($groupedRequests as $departmentName => $requests)
                        <div class="leave-request-card mb-3">
                            <div class="card-head">
                                <h2>
                                    <i class="fas fa-sitemap"></i> {{ $departmentName }}
                                    <span class="badge badge-secondary ml-2">{{ $requests->count() }}
                                        {{ $requests->count() == 1 ? 'Request' : 'Requests' }}</span>
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th width="40" class="text-center">#</th>
                                                <th width="100">NIK</th>
                                                <th>Employee Name</th>
                                                <th>Position</th>
                                                <th>Project</th>
                                                <th width="100">Start Date</th>
                                                <th width="100">End Date</th>
                                                <th width="60" class="text-center">Days</th>
                                                <th width="100" class="text-center">Status</th>
                                                <th width="60" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($requests as $index => $request)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                                                    <td><strong>{{ $request->administration->nik ?? '-' }}</strong></td>
                                                    <td>{{ $request->employee->fullname ?? '-' }}</td>
                                                    <td><small>{{ $request->administration->position->position_name ?? '-' }}</small>
                                                    </td>
                                                    <td><small>{{ $request->administration->project->project_code ?? '-' }}</small>
                                                    </td>
                                                    <td><small>{{ \Carbon\Carbon::parse($request->start_date)->format('d M Y') }}</small>
                                                    </td>
                                                    <td><small>{{ \Carbon\Carbon::parse($request->end_date)->format('d M Y') }}</small>
                                                    </td>
                                                    <td class="text-center"><span
                                                            class="badge badge-info">{{ $request->total_days }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($request->status == 'pending')
                                                            <span class="badge badge-warning">Pending</span>
                                                        @elseif($request->status == 'approved')
                                                            <span class="badge badge-success">Approved</span>
                                                        @elseif($request->status == 'rejected')
                                                            <span class="badge badge-danger">Rejected</span>
                                                        @elseif($request->status == 'cancelled')
                                                            <span class="badge badge-secondary">Cancelled</span>
                                                        @elseif($request->status == 'auto_approved')
                                                            <span class="badge badge-info">Auto Approved</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('leave.requests.show', $request->id) }}"
                                                            class="btn btn-sm btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Custom Styles for Leave Request Detail */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .leave-request-header {
            position: relative;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .leave-request-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .leave-request-project {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .leave-request-number {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .leave-request-date {
            font-size: 14px;
            opacity: 0.9;
        }

        .leave-request-status-pill {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .leave-request-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content Styles */
        .leave-request-content {
            padding: 0 20px;
        }

        /* Cards */
        .leave-request-card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-head {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .card-head.bg-danger {
            background-color: #dc3545 !important;
            color: white;
        }

        .card-head.bg-danger h2 {
            color: white;
        }

        .card-head h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 20px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 20px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            background-color: #3498db;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #777;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        /* Table Styling */
        .table-sm th,
        .table-sm td {
            padding: 0.75rem;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #5a5c69;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Action Buttons */
        .leave-request-action-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            gap: 8px;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            width: 100%;
            min-height: 44px;
        }

        .back-btn {
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #5a6268;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .leave-request-content .row {
                display: flex;
                flex-direction: column;
            }

            .leave-request-content .col-lg-8 {
                order: 1;
                width: 100%;
            }

            .leave-request-content .col-lg-4 {
                order: 2;
                width: 100%;
            }

            .leave-request-content {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .leave-request-header {
                height: auto;
                padding: 15px;
                position: relative;
            }

            .leave-request-header-content {
                padding-right: 80px;
            }

            .leave-request-number {
                font-size: 20px;
            }

            .leave-request-status-pill {
                position: absolute;
                top: 15px;
                right: 15px;
                margin-top: 0;
                align-self: flex-start;
            }

            .card-body {
                padding: 15px;
            }

            .info-item {
                padding: 10px 0;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        function cancelBatch() {
            Swal.fire({
                title: 'Cancel Batch?',
                text: 'This will cancel all pending leave requests in this batch. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Cancel Batch',
                cancelButtonText: 'No, Keep Batch',
                input: 'textarea',
                inputPlaceholder: 'Enter cancellation reason...',
                inputAttributes: {
                    'aria-label': 'Enter cancellation reason'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to provide a cancellation reason!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('leave.periodic-requests.cancel', $batchInfo['batch_id']) }}';

                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    // Add cancellation reason
                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'cancellation_reason';
                    reasonInput.value = result.value;
                    form.appendChild(reasonInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endsection
