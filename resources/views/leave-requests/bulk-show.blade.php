@extends('layouts.main')

@section('title', 'Bulk Leave Request Detail')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Bulk Leave Request Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.bulk-requests.index') }}">Bulk Leave Requests</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Batch Information -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group mr-2"></i>
                                <strong>Batch Information</strong>
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.bulk-requests.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Batch ID:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-info badge-lg">{{ $batchInfo['batch_id'] }}</span>
                                        </dd>

                                        <dt class="col-sm-4">Total Requests:</dt>
                                        <dd class="col-sm-8">
                                            <strong>{{ $batchInfo['total_requests'] }}</strong> leave requests
                                        </dd>

                                        <dt class="col-sm-4">Created At:</dt>
                                        <dd class="col-sm-8">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ $batchInfo['created_at']->format('d M Y H:i') }}
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Created By:</dt>
                                        <dd class="col-sm-8">
                                            <i class="fas fa-user mr-1"></i>
                                            {{ $batchInfo['requested_by']->name ?? 'N/A' }}
                                        </dd>

                                        <dt class="col-sm-4">Notes:</dt>
                                        <dd class="col-sm-8">
                                            @if($batchInfo['bulk_notes'])
                                                <div class="alert alert-info mb-0 py-2">
                                                    <i class="fas fa-sticky-note mr-1"></i>
                                                    {{ $batchInfo['bulk_notes'] }}
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Summary -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $batchInfo['total_requests'] }}</h3>
                            <p>Total Requests</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $statusCounts->get('pending', 0) }}</h3>
                            <p>Pending</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $statusCounts->get('approved', 0) }}</h3>
                            <p>Approved</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $statusCounts->get('rejected', 0) + $statusCounts->get('cancelled', 0) }}</h3>
                            <p>Rejected/Cancelled</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Requests List -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                <strong>Leave Requests in This Batch</strong>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th>NIK</th>
                                            <th>Employee Name</th>
                                            <th>Position</th>
                                            <th>Department</th>
                                            <th>Project</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                            <th width="100" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leaveRequests as $index => $request)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $request->administration->nik ?? '-' }}</strong>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user mr-1"></i>
                                                    {{ $request->employee->fullname ?? '-' }}
                                                </td>
                                                <td>{{ $request->administration->position->position_name ?? '-' }}</td>
                                                <td>{{ $request->administration->position->department->department_name ?? '-' }}</td>
                                                <td>
                                                    <small>{{ $request->administration->project->project_code ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($request->start_date)->format('d M Y') }}
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($request->end_date)->format('d M Y') }}
                                                </td>
                                                <td>
                                                    <strong>{{ $request->total_days }}</strong>
                                                </td>
                                                <td>
                                                    @if($request->status == 'pending')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock mr-1"></i>Pending
                                                        </span>
                                                    @elseif($request->status == 'approved')
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i>Approved
                                                        </span>
                                                    @elseif($request->status == 'rejected')
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times mr-1"></i>Rejected
                                                        </span>
                                                    @elseif($request->status == 'cancelled')
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-ban mr-1"></i>Cancelled
                                                        </span>
                                                    @elseif($request->status == 'auto_approved')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-robot mr-1"></i>Auto Approved
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('leave.requests.show', $request->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="View Request Details">
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
                </div>
            </div>

            <!-- Batch Actions -->
            @if($statusCounts->get('pending', 0) > 0)
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-danger card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Batch Actions</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    You can cancel all pending requests in this batch at once.
                                </p>
                                <button type="button" class="btn btn-danger" onclick="cancelBatch()">
                                    <i class="fas fa-ban mr-1"></i> Cancel All Pending Requests
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        .small-box .icon {
            font-size: 50px;
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
                    form.action = '{{ route("leave.bulk-requests.cancel", $batchInfo["batch_id"]) }}';
                    
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

