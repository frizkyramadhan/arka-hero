@extends('layouts.main')

@section('title', $title ?? 'Flight Request Details')

@section('content')
    <div class="content-wrapper-custom">
        <div class="flight-request-header">
            <div class="flight-request-header-content">
                <div class="flight-request-project">
                    {{ $flightRequest->project ?? ($flightRequest->administration && $flightRequest->administration->project ? $flightRequest->administration->project->project_name : 'N/A') }}
                </div>
                <h1 class="flight-request-number">{{ $flightRequest->form_number ?? 'Flight Request' }}</h1>
                <div class="flight-request-date">
                    <i class="far fa-calendar-alt"></i>
                    {{ $flightRequest->requested_at ? $flightRequest->requested_at->format('d F Y') : $flightRequest->created_at->format('d F Y') }}
                </div>
                @php
                    $statusMap = [
                        'draft' => ['label' => 'Draft', 'class' => 'badge badge-secondary', 'icon' => 'fa-edit'],
                        'submitted' => [
                            'label' => 'Submitted',
                            'class' => 'badge badge-info',
                            'icon' => 'fa-paper-plane',
                        ],
                        'approved' => [
                            'label' => 'Approved',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'issued' => [
                            'label' => 'Issued',
                            'class' => 'badge badge-primary',
                            'icon' => 'fa-file-invoice',
                        ],
                        'completed' => [
                            'label' => 'Completed',
                            'class' => 'badge badge-dark',
                            'icon' => 'fa-check-double',
                        ],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge badge-warning', 'icon' => 'fa-ban'],
                    ];
                    $status = $flightRequest->status;
                    $pill = $statusMap[$status] ?? [
                        'label' => ucfirst($status),
                        'class' => 'badge badge-secondary',
                        'icon' => 'fa-question-circle',
                    ];
                @endphp
                <div class="flight-request-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flight-request-content">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Employee Information -->
                    <div class="flight-request-card employee-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user"></i> Employee Information</h2>
                        </div>
                        <div class="card-body">
                            @php
                                $employee = $flightRequest->employee;
                                $administration =
                                    $flightRequest->administration ??
                                    ($employee ? $employee->activeAdministration : null);
                                $name = $flightRequest->employee_name ?? ($employee ? $employee->fullname : 'N/A');
                                $nik = $flightRequest->nik ?? ($administration ? $administration->nik : 'N/A');
                                $position =
                                    $flightRequest->position ??
                                    ($administration && $administration->position
                                        ? $administration->position->position_name
                                        : 'N/A');
                                $department =
                                    $flightRequest->department ??
                                    ($administration &&
                                    $administration->position &&
                                    $administration->position->department
                                        ? $administration->position->department->department_name
                                        : 'N/A');
                                $project =
                                    $flightRequest->project ??
                                    ($administration && $administration->project
                                        ? $administration->project->project_name
                                        : 'N/A');
                                $projectCode =
                                    $administration && $administration->project
                                        ? $administration->project->project_code
                                        : null;
                                $projectNumber = $projectCode ? $projectCode . ' - ' . $project : $project;
                                $phoneNumber =
                                    $flightRequest->phone_number ??
                                    ($administration ? $administration->phone_number : null);
                                $poh = $administration && $administration->poh ? $administration->poh : 'N/A';
                                $doh =
                                    $administration && $administration->doh
                                        ? \Carbon\Carbon::parse($administration->doh)->format('d F Y')
                                        : 'N/A';
                            @endphp
                            <div class="employee-info-table">
                                <div class="info-row">
                                    <div class="info-label"><strong>NAME:</strong></div>
                                    <div class="info-value">{{ $name }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>REQUEST TYPE:</strong></div>
                                    <div class="info-value">
                                        @if ($flightRequest->request_type === 'leave_based' && $flightRequest->leaveRequest)
                                            @php
                                                $leave = $flightRequest->leaveRequest;
                                                $leaveEmployee = $leave->employee;
                                                $leaveAdmin =
                                                    $leave->administration ??
                                                    ($leaveEmployee ? $leaveEmployee->activeAdministration : null);
                                            @endphp
                                            Leave Request (Cuti) - {{ $leaveEmployee ? $leaveEmployee->fullname : 'N/A' }} -
                                            {{ $leaveAdmin ? $leaveAdmin->nik : 'N/A' }}
                                            ({{ $leave->start_date->format('d M Y') }} to
                                            {{ $leave->end_date->format('d M Y') }})
                                        @elseif ($flightRequest->request_type === 'travel_based' && $flightRequest->officialTravel)
                                            @php
                                                $travel = $flightRequest->officialTravel;
                                                $traveler = $travel->traveler;
                                                $travelEmployee = $traveler ? $traveler->employee : null;
                                            @endphp
                                            Official Travel (LOT) - {{ $travel->official_travel_number ?? 'N/A' }} -
                                            {{ $travelEmployee ? $travelEmployee->fullname : 'N/A' }}
                                            ({{ $travel->destination }})
                                        @else
                                            Standalone
                                        @endif
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>ID NUMBER / NIK:</strong></div>
                                    <div class="info-value">{{ $nik }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>POSITION:</strong></div>
                                    <div class="info-value">{{ $position }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>DEPT/DIVISION:</strong></div>
                                    <div class="info-value">{{ $department }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>POH:</strong></div>
                                    <div class="info-value">{{ $poh }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>DOH:</strong></div>
                                    <div class="info-value">{{ $doh }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>PROJECT NUMBER:</strong></div>
                                    <div class="info-value">{{ $projectNumber }}</div>
                                </div>
                                @if ($phoneNumber)
                                    <div class="info-row">
                                        <div class="info-label"><strong>PHONE NUMBER:</strong></div>
                                        <div class="info-value">{{ $phoneNumber }}</div>
                                    </div>
                                @endif
                                <div class="info-row">
                                    <div class="info-label"><strong>PURPOSE OF TRAVEL:</strong></div>
                                    <div class="info-value">{{ $flightRequest->purpose_of_travel }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>TOTAL TRAVEL DAYS:</strong></div>
                                    <div class="info-value">{{ $flightRequest->total_travel_days ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flight Details -->
                    <div class="flight-request-card flight-details-card">
                        <div class="card-head">
                            <h2><i class="fas fa-route"></i> Flight Details</h2>
                        </div>
                        <div class="card-body">
                            @php
                                $orderedFlightDetails = $flightRequest->details
                                    ->sortBy(['segment_order', 'flight_date'])
                                    ->values();
                            @endphp
                            @if ($orderedFlightDetails->count() > 0)
                                <div class="flight-details-list">
                                    @foreach ($orderedFlightDetails as $index => $detail)
                                        <div class="flight-detail-card">
                                            <div class="flight-detail-header">
                                                <h4>Flight {{ $index + 1 }}</h4>
                                            </div>
                                            <div class="flight-detail-content">
                                                <div class="flight-detail-grid">
                                                    <div class="flight-detail-item">
                                                        <div class="flight-detail-icon" style="background-color: #e74c3c;">
                                                            <i class="fas fa-plane-departure"></i>
                                                        </div>
                                                        <div class="flight-detail-info">
                                                            <div class="flight-detail-label">Departure City</div>
                                                            <div class="flight-detail-value">{{ $detail->departure_city }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flight-detail-item">
                                                        <div class="flight-detail-icon" style="background-color: #27ae60;">
                                                            <i class="fas fa-plane-arrival"></i>
                                                        </div>
                                                        <div class="flight-detail-info">
                                                            <div class="flight-detail-label">Arrival City</div>
                                                            <div class="flight-detail-value">{{ $detail->arrival_city }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flight-detail-item">
                                                        <div class="flight-detail-icon" style="background-color: #3498db;">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </div>
                                                        <div class="flight-detail-info">
                                                            <div class="flight-detail-label">Flight Date</div>
                                                            <div class="flight-detail-value">
                                                                {{ $detail->flight_date->format('d F Y') }}</div>
                                                        </div>
                                                    </div>
                                                    @if ($detail->flight_time)
                                                        <div class="flight-detail-item">
                                                            <div class="flight-detail-icon"
                                                                style="background-color: #9b59b6;">
                                                                <i class="fas fa-clock"></i>
                                                            </div>
                                                            <div class="flight-detail-info">
                                                                <div class="flight-detail-label">Flight Time</div>
                                                                <div class="flight-detail-value">
                                                                    {{ \Carbon\Carbon::parse($detail->flight_time)->format('H:i') }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($detail->airline)
                                                        <div class="flight-detail-item">
                                                            <div class="flight-detail-icon"
                                                                style="background-color: #f1c40f;">
                                                                <i class="fas fa-plane"></i>
                                                            </div>
                                                            <div class="flight-detail-info">
                                                                <div class="flight-detail-label">Airline</div>
                                                                <div class="flight-detail-value">{{ $detail->airline }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-flight-details">
                                    <i class="fas fa-plane-slash"></i>
                                    <p>No flight details available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Notes -->
                    @if ($flightRequest->notes)
                        <div class="flight-request-card notes-card">
                            <div class="card-head">
                                <h2><i class="fas fa-sticky-note"></i> Notes</h2>
                            </div>
                            <div class="card-body" style="text-align: left !important;">
                                <pre
                                    style="text-align: left; white-space: pre-wrap; font-family: inherit; margin: 0; padding: 0; background: transparent; border: none;">{{ $flightRequest->notes }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- Manual Approvers Card - sembunyikan jika FR completed/rejected/cancelled -->
                    @if (
                        !empty($flightRequest->manual_approvers) &&
                            !in_array($flightRequest->status, ['completed', 'rejected', 'cancelled']))
                        <div class="flight-request-card mb-4">
                            <div class="card-head">
                                <h2><i class="fas fa-users"></i> Selected Approvers</h2>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => $flightRequest->manual_approvers ?? [],
                                    'mode' => 'view',
                                    'documentType' => 'flight_request',
                                    'documentId' => $flightRequest->id,
                                ])
                            </div>
                        </div>
                    @endif

                    <!-- Letter of Guarantee (LG) - 1 FR dapat memiliki beberapa LG -->
                    @if (in_array($flightRequest->status, ['approved', 'issued']) || $flightRequest->issuances->count() > 0)
                        <div class="flight-request-card issuances-card">
                            <div class="card-head card-head-lg">
                                <h2><i class="fas fa-file-invoice"></i> Letter of Guarantee (LG)</h2>
                                @if (in_array($flightRequest->status, ['approved', 'issued']))
                                    @can('flight-issuances.create')
                                        @if ($flightRequest->canBeIssued())
                                            <a href="{{ route('flight-issuances.create', ['flight_request_id' => $flightRequest->id]) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Add LG
                                            </a>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                            <div class="card-body">
                                @if ($flightRequest->issuances->count() > 0)
                                    <div class="issuances-list">
                                        @foreach ($flightRequest->issuances as $issuance)
                                            <div class="issuance-item">
                                                <div class="issuance-header">
                                                    <h5>{{ $issuance->issued_number ?? 'LG-' . $issuance->id }}</h5>
                                                    <a href="{{ route('flight-issuances.show', $issuance->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                </div>
                                                <div class="issuance-details">
                                                    <div class="issuance-detail-item">
                                                        <i class="fas fa-calendar"></i>
                                                        <span><strong>Issued Date:</strong>
                                                            {{ $issuance->issued_date ? $issuance->issued_date->format('d F Y') : '-' }}</span>
                                                    </div>
                                                    <div class="issuance-detail-item">
                                                        <i class="fas fa-building"></i>
                                                        <span><strong>Business Partner:</strong>
                                                            {{ $issuance->businessPartner->bp_name ?? '-' }}</span>
                                                    </div>
                                                    <div class="issuance-detail-item">
                                                        <i class="fas fa-ticket-alt"></i>
                                                        <span><strong>Total Tickets:</strong>
                                                            {{ $issuance->issuanceDetails->count() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-info-circle"></i> No Letter of Guarantee yet. Use <strong>Add
                                            LG</strong> above to create one.
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flight-request-card actions-card">
                        <div class="card-head">
                            <h2><i class="fas fa-tasks"></i> Actions</h2>
                        </div>
                        <div class="card-body">
                            <div class="actions-list">
                                <a href="{{ route('flight-requests.index') }}" class="btn-action back-btn">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>

                                @if ($flightRequest->status != 'cancelled')
                                    @if ($flightRequest->status == 'draft')
                                        @can('flight-requests.edit')
                                            <a href="{{ route('flight-requests.edit', $flightRequest->id) }}"
                                                class="btn-action edit-btn">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        @endcan

                                        @can('flight-requests.delete')
                                            <button type="button" class="btn-action delete-btn btn-dark" data-toggle="modal"
                                                data-target="#deleteModal">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        @endcan

                                        <!-- Submit for Approval button -->
                                        <button type="button" class="btn-action submit-btn" data-toggle="modal"
                                            data-target="#submitModal">
                                            <i class="fas fa-paper-plane"></i> Submit for Approval
                                        </button>
                                    @endif


                                    @if ($flightRequest->canBeCancelled())
                                        <button type="button" class="btn-action cancel-btn" data-toggle="modal"
                                            data-target="#cancelModal">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    @endif

                                    @if ($flightRequest->status == 'issued')
                                        <button type="button" class="btn-action complete-btn" data-toggle="modal"
                                            data-target="#completeModal">
                                            <i class="fas fa-check-double"></i> Complete
                                        </button>
                                    @endif
                                @endif

                                <a href="{{ route('flight-requests.print', $flightRequest->id) }}"
                                    class="btn btn-primary" target="_blank">
                                    <i class="fas fa-print"></i> Print
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    @if ($flightRequest->status == 'draft')
        <div class="modal fade custom-modal" id="deleteModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Flight Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p class="delete-message">Are you sure you want to delete this flight request?</p>
                        <p class="delete-warning">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('flight-requests.destroy', $flightRequest->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Submit for Approval Modal -->
    @if ($flightRequest->status == 'draft')
        <div class="modal fade custom-modal" id="submitModal" tabindex="-1" role="dialog"
            aria-labelledby="submitModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="submitModalLabel">Submit for Approval</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="submit-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="submit-message">
                            Are you sure you want to submit this Flight Request for approval?
                        </div>
                        <div class="submit-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            This action will submit the document to the approval workflow and cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('flight-requests.submit', $flightRequest->id) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Submit for Approval
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Cancel Modal -->
    @if ($flightRequest->canBeCancelled())
        <div class="modal fade custom-modal" id="cancelModal" tabindex="-1" role="dialog"
            aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('flight-requests.cancel', $flightRequest->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelModalLabel">Cancel Flight Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Reason for Cancellation <span class="text-danger">*</span></label>
                                <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Cancel Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Complete Modal -->
    @if ($flightRequest->status == 'issued')
        <div class="modal fade custom-modal" id="completeModal" tabindex="-1" role="dialog"
            aria-labelledby="completeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('flight-requests.complete', $flightRequest->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="completeModalLabel">Complete Flight Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">Mark this Flight Request as completed? This indicates the travel has been
                                completed.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-double"></i> Complete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('styles')
    <style>
        /* Custom Styles for Flight Request Detail */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .flight-request-header {
            position: relative;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .flight-request-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .flight-request-project {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .flight-request-number {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .flight-request-date {
            font-size: 14px;
            opacity: 0.9;
        }

        .flight-request-status-pill {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .flight-request-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content Styles */
        .flight-request-content {
            padding: 0 20px;
        }

        /* Cards */
        .flight-request-card {
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
            flex-shrink: 0;
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
            font-size: 14px;
        }

        /* Employee Card - Official Travel Detail Style (Compact) */
        .employee-card {
            overflow: hidden;
        }

        .employee-card .card-body {
            padding: 15px 20px;
        }

        .employee-info-table {
            width: 100%;
        }

        .employee-info-table .info-row {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            min-height: 32px;
        }

        .employee-info-table .info-row:last-child {
            border-bottom: none;
        }

        .employee-info-table .info-row:first-child {
            padding-top: 0;
        }

        .employee-info-table .info-label {
            flex: 0 0 180px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 13px;
            text-align: left;
            line-height: 1.4;
        }

        .employee-info-table .info-value {
            flex: 1;
            color: #333;
            font-size: 13px;
            text-align: left;
            padding-left: 8px;
            line-height: 1.4;
        }

        /* Flight Details Card */
        .flight-details-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .flight-detail-card {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }

        .flight-detail-header {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .flight-detail-header h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .flight-detail-content {
            padding: 15px;
        }

        .flight-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .flight-detail-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .flight-detail-icon {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            flex-shrink: 0;
        }

        .flight-detail-info {
            flex: 1;
        }

        .flight-detail-label {
            font-size: 11px;
            color: #777;
            margin-bottom: 2px;
        }

        .flight-detail-value {
            font-weight: 500;
            color: #333;
            font-size: 13px;
        }

        .no-flight-details {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .no-flight-details i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        .no-flight-details p {
            margin: 0;
            font-size: 16px;
        }

        /* Notes Card - simple left align */
        .notes-card .card-body {
            text-align: left !important;
        }

        .notes-card pre {
            text-align: left !important;
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
            font-family: inherit !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
        }

        /* LG Card - shadow menonjol */
        .issuances-card {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12), 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .card-head-lg {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-head-lg h2 {
            margin: 0;
        }

        /* Issuances Card list */
        .issuances-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .issuance-item {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            background-color: #f8f9fa;
        }

        .issuance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .issuance-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .issuance-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .issuance-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #555;
        }

        .issuance-detail-item i {
            color: #3498db;
            width: 16px;
        }

        /* Actions Card */
        .actions-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            gap: 8px;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .back-btn {
            background-color: #64748b;
        }

        .back-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .edit-btn {
            background-color: #3498db;
        }

        .edit-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .submit-btn {
            background-color: #27ae60;
        }

        .submit-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .issue-btn {
            background-color: #3498db;
        }

        .issue-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .cancel-btn {
            background-color: #e74c3c;
        }

        .cancel-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .complete-btn {
            background-color: #27ae60;
        }

        .complete-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .delete-btn {
            background-color: #343a40;
        }

        .delete-btn:hover {
            background-color: #23272b;
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Delete Modal Styles */
        .delete-icon {
            text-align: center;
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 15px;
        }

        .delete-message {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }

        .delete-warning {
            font-size: 13px;
            color: #64748b;
            text-align: center;
        }

        .btn-cancel {
            padding: 8px 16px;
            border-radius: 4px;
            background: #e2e8f0;
            color: #475569;
            font-weight: 500;
            border: none;
        }

        .btn-cancel:hover {
            background: #cbd5e1;
        }

        .btn-confirm-delete {
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #e74c3c;
            color: white;
            font-weight: 500;
            border: none;
        }

        .btn-confirm-delete:hover {
            background-color: #c0392b;
        }

        /* Submit Modal Styles */
        .submit-icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 15px;
            color: #28a745;
        }

        .submit-message {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }

        .submit-warning {
            font-size: 13px;
            color: #e74c3c;
            text-align: center;
        }

        /* Custom Modal */
        .custom-modal .modal-content {
            border-radius: 6px;
            border: none;
        }

        .custom-modal .modal-header {
            background: #f8fafc;
            padding: 15px 20px;
        }

        .custom-modal .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .flight-detail-grid {
                grid-template-columns: 1fr;
            }

            .flight-request-content .row {
                display: flex;
                flex-direction: column;
            }

            .flight-request-content .col-lg-8 {
                order: 1;
                width: 100%;
            }

            .flight-request-content .col-lg-4 {
                order: 2;
                width: 100%;
            }

            .flight-request-card {
                margin-bottom: 20px;
            }

            .flight-request-content {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .flight-request-header {
                height: auto;
                padding: 15px;
                position: relative;
            }

            .flight-request-header-content {
                padding-right: 80px;
            }

            .flight-request-number {
                font-size: 20px;
            }

            .flight-request-status-pill {
                position: absolute;
                top: 15px;
                right: 15px;
                margin-top: 0;
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
        function showCancelModal() {
            $('#cancelModal').modal('show');
        }
    </script>
@endsection
