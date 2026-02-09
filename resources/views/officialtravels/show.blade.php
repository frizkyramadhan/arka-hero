@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">{{ $officialtravel->project->project_name ?? 'No Project' }}</div>
                <h1 class="travel-destination">{{ $officialtravel->official_travel_number }}</h1>
                <div class="travel-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($officialtravel->official_travel_date)) }}
                </div>
                @php
                    $statusMap = [
                        'draft' => ['label' => 'Draft', 'class' => 'badge badge-secondary', 'icon' => 'fa-edit'],
                        'pending_hr' => ['label' => 'Menunggu Konfirmasi HR', 'class' => 'badge badge-warning', 'icon' => 'fa-clock'],
                        'submitted' => [
                            'label' => 'Submitted',
                            'class' => 'badge badge-info',
                            'icon' => 'fa-paper-plane',
                        ],
                        'approved' => ['label' => 'Open', 'class' => 'badge badge-success', 'icon' => 'fa-plane'],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                        'closed' => [
                            'label' => 'Closed',
                            'class' => 'badge badge-primary',
                            'icon' => 'fa-check-circle',
                        ],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge badge-warning', 'icon' => 'fa-ban'],
                    ];
                    $status = $officialtravel->status;
                    $pill = $officialtravel->isPendingHr()
                        ? $statusMap['pending_hr']
                        : ($statusMap[$status] ?? [
                            'label' => ucfirst($status),
                            'class' => 'badge badge-secondary',
                            'icon' => 'fa-question-circle',
                        ]);
                @endphp
                <div class="travel-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="travel-content">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Main Travel Info -->
                    <div class="travel-card travel-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-info-circle"></i> Travel Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                {{-- <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Origin Project</div>
                                        <div class="info-value">{{ $officialtravel->project->project_name ?? 'No Project' }}</div>
                                    </div>
                                </div> --}}
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Destination</div>
                                        <div class="info-value">{{ $officialtravel->destination }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Purpose</div>
                                        <div class="info-value">{{ $officialtravel->purpose }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Duration</div>
                                        <div class="info-value">{{ $officialtravel->duration }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Departure Date</div>
                                        <div class="info-value">
                                            {{ date('d F Y', strtotime($officialtravel->departure_from)) }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Transportation</div>
                                        <div class="info-value">
                                            {{ $officialtravel->transportation->transportation_name ?? 'No Transportation' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-hotel"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Accommodation</div>
                                        <div class="info-value">
                                            {{ $officialtravel->accommodation->accommodation_name ?? 'No Accommodation' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Traveler Info -->
                    <div class="travel-card traveler-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user"></i> Traveler</h2>
                        </div>
                        <div class="traveler-details">
                            <div class="traveler-detail-item">
                                <i class="fas fa-id-card detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">NIK - Name</div>
                                    <div class="detail-value">{{ $officialtravel->traveler->nik }} -
                                        {{ $officialtravel->traveler->employee->fullname ?? 'Unknown Employee' }}
                                    </div>
                                </div>
                            </div>
                            <div class="traveler-detail-item">
                                <i class="fas fa-sitemap detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Title</div>
                                    <div class="detail-value">
                                        {{ $officialtravel->traveler->position->position_name ?? 'No Position' }}
                                    </div>
                                </div>
                            </div>
                            <div class="traveler-detail-item">
                                <i class="fas fa-globe detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Business Unit</div>
                                    <div class="detail-value">
                                        {{ $officialtravel->traveler->project->project_code ?? 'No Code' }} :
                                        {{ $officialtravel->traveler->project->project_name ?? 'No Project' }}</div>
                                </div>
                            </div>
                            <div class="traveler-detail-item">
                                <i class="fas fa-building detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Division / Department</div>
                                    <div class="detail-value">
                                        {{ $officialtravel->traveler->position->department->department_name ?? 'No Department' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Followers List -->
                    @if ($officialtravel->details->isNotEmpty())
                        <div class="travel-card followers-card">
                            <div class="card-head">
                                <h2><i class="fas fa-users"></i> Followers <span
                                        class="followers-count">{{ $officialtravel->details->count() }}</span></h2>
                            </div>
                            <div class="card-body p-0">
                                <div class="followers-list">
                                    @foreach ($officialtravel->details as $detail)
                                        <div class="follower-item">
                                            <div class="follower-info">
                                                <div class="follower-name">
                                                    {{ $detail->follower->employee->fullname ?? 'Unknown Employee' }}
                                                </div>
                                                <div class="follower-position">
                                                    {{ $detail->follower->position->position_name ?? 'No Position' }}</div>
                                                <div class="follower-meta">
                                                    <span class="follower-nik"><i class="fas fa-id-card"></i>
                                                        {{ $detail->follower->nik }}</span>
                                                    <span class="follower-department"><i class="fas fa-sitemap"></i>
                                                        {{ $detail->follower->position->department->department_name ?? 'No Department' }}</span>
                                                </div>
                                                <div class="follower-project">
                                                    <i class="fas fa-project-diagram"></i>
                                                    {{ $detail->follower->project->project_code ?? 'No Code' }} :
                                                    {{ $detail->follower->project->project_name ?? 'No Project' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Stops Timeline -->
                    <div class="travel-card stops-timeline-card">
                        <div class="card-head">
                            <h2><i class="fas fa-route"></i> Travel Stops Timeline</h2>
                        </div>
                        <div class="card-body">
                            @if ($officialtravel->stops->count() > 0)
                                <div class="timeline">
                                    @foreach ($officialtravel->stops as $index => $stop)
                                        @if ($stop->isComplete())
                                            <!-- Complete Stop - Collapsible Accordion -->
                                            <div class="timeline-item complete">
                                                <div class="timeline-marker">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <h4>Stop #{{ $index + 1 }}</h4>
                                                        <span class="timeline-status">
                                                            <span class="badge badge-success">Complete</span>
                                                        </span>
                                                    </div>

                                                    <!-- Accordion Toggle -->
                                                    <div class="accordion" id="stopAccordion{{ $index + 1 }}">
                                                        <div class="card">
                                                            <div class="card-header" id="heading{{ $index + 1 }}">
                                                                <h5 class="mb-0">
                                                                    <button
                                                                        class="btn btn-link btn-block text-left collapsed"
                                                                        type="button" data-toggle="collapse"
                                                                        data-target="#collapse{{ $index + 1 }}"
                                                                        aria-expanded="false"
                                                                        aria-controls="collapse{{ $index + 1 }}">
                                                                        <i class="fas fa-chevron-down accordion-icon"></i>
                                                                        <span class="accordion-title">View Details</span>
                                                                    </button>
                                                                </h5>
                                                            </div>
                                                            <div id="collapse{{ $index + 1 }}" class="collapse"
                                                                aria-labelledby="heading{{ $index + 1 }}"
                                                                data-parent="#stopAccordion{{ $index + 1 }}">
                                                                <div class="card-body">
                                                                    <div class="timeline-details">
                                                                        @if ($stop->hasArrival())
                                                                            <div class="timeline-detail-item">
                                                                                <i
                                                                                    class="fas fa-plane-arrival text-success"></i>
                                                                                <div class="detail-content">
                                                                                    <div class="detail-label">Arrival</div>
                                                                                    <div class="detail-value">
                                                                                        {{ $stop->arrival_at_destination ? date('d F Y H:i', strtotime($stop->arrival_at_destination)) : 'Not recorded' }}
                                                                                        @if ($stop->arrivalChecker)
                                                                                            <br><small
                                                                                                class="text-muted">by
                                                                                                {{ $stop->arrivalChecker->name }}</small>
                                                                                        @endif
                                                                                        @if ($stop->arrival_remark)
                                                                                            <br><small
                                                                                                class="text-muted">{{ $stop->arrival_remark }}</small>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif

                                                                        @if ($stop->hasDeparture())
                                                                            <div class="timeline-detail-item">
                                                                                <i
                                                                                    class="fas fa-plane-departure text-danger"></i>
                                                                                <div class="detail-content">
                                                                                    <div class="detail-label">Departure
                                                                                    </div>
                                                                                    <div class="detail-value">
                                                                                        {{ $stop->departure_from_destination ? date('d F Y H:i', strtotime($stop->departure_from_destination)) : 'Not recorded' }}
                                                                                        @if ($stop->departureChecker)
                                                                                            <br><small
                                                                                                class="text-muted">by
                                                                                                {{ $stop->departureChecker->name }}</small>
                                                                                        @endif
                                                                                        @if ($stop->departure_remark)
                                                                                            <br><small
                                                                                                class="text-muted">{{ $stop->departure_remark }}</small>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <!-- Incomplete Stop - Regular Display -->
                                            <div
                                                class="timeline-item {{ $stop->hasArrival() ? 'arrival-only' : 'departure-only' }}">
                                                <div class="timeline-marker">
                                                    <i
                                                        class="fas {{ $stop->hasArrival() ? 'fa-plane-arrival' : 'fa-plane-departure' }}"></i>
                                                </div>
                                                <div class="timeline-content">
                                                    <div class="timeline-header">
                                                        <h4>Stop #{{ $index + 1 }}</h4>
                                                        <span class="timeline-status">
                                                            @if ($stop->hasArrival())
                                                                <span class="badge badge-warning">Arrival Only</span>
                                                            @else
                                                                <span class="badge badge-info">Departure Only</span>
                                                            @endif
                                                        </span>
                                                    </div>

                                                    <div class="timeline-details">
                                                        @if ($stop->hasArrival())
                                                            <div class="timeline-detail-item">
                                                                <i class="fas fa-plane-arrival text-success"></i>
                                                                <div class="detail-content">
                                                                    <div class="detail-label">Arrival</div>
                                                                    <div class="detail-value">
                                                                        {{ $stop->arrival_at_destination ? date('d F Y H:i', strtotime($stop->arrival_at_destination)) : 'Not recorded' }}
                                                                        @if ($stop->arrivalChecker)
                                                                            <br><small class="text-muted">by
                                                                                {{ $stop->arrivalChecker->name }}</small>
                                                                        @endif
                                                                        @if ($stop->arrival_remark)
                                                                            <br><small
                                                                                class="text-muted">{{ $stop->arrival_remark }}</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($stop->hasDeparture())
                                                            <div class="timeline-detail-item">
                                                                <i class="fas fa-plane-departure text-danger"></i>
                                                                <div class="detail-content">
                                                                    <div class="detail-label">Departure</div>
                                                                    <div class="detail-value">
                                                                        {{ $stop->departure_from_destination ? date('d F Y H:i', strtotime($stop->departure_from_destination)) : 'Not recorded' }}
                                                                        @if ($stop->departureChecker)
                                                                            <br><small class="text-muted">by
                                                                                {{ $stop->departureChecker->name }}</small>
                                                                        @endif
                                                                        @if ($stop->departure_remark)
                                                                            <br><small
                                                                                class="text-muted">{{ $stop->departure_remark }}</small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="no-stops">
                                    <i class="fas fa-route text-muted"></i>
                                    <p class="text-muted">No stops recorded yet</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Manual Approvers Card -->
                    @if (!empty($officialtravel->manual_approvers))
                        <div class="travel-card">
                            <div class="card-head">
                                <h2><i class="fas fa-users"></i> Approval Status</h2>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => $officialtravel->manual_approvers ?? [],
                                    'mode' => 'view',
                                    'documentType' => 'officialtravel',
                                    'documentId' => $officialtravel->id,
                                ])
                            </div>
                        </div>
                    @endif

                    <!-- Approval Status Card -->
                    {{-- <x-approval-status-card :documentType="'officialtravel'" :documentId="$officialtravel->id" :mode="$officialtravel->status === 'draft' ? 'preview' : 'status'" :projectId="$officialtravel->official_travel_origin"
                        :departmentId="$officialtravel->traveler->position->department_id ?? null" title="Approval Status" /> --}}

                    <!-- Action Buttons -->
                    <div class="travel-action-buttons">
                        <a href="{{ route('officialtravels.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>

                        @if ($officialtravel->status != 'canceled')
                            @if ($officialtravel->status == 'draft')
                                @can('official-travels.edit')
                                    <a href="{{ route('officialtravels.edit', $officialtravel->id) }}"
                                        class="btn-action edit-btn">
                                        <i class="fas fa-edit"></i> {{ $officialtravel->isPendingHr() ? 'Konfirmasi & Isi Nomor Surat' : 'Edit' }}
                                    </a>
                                @endcan

                                @if ($officialtravel->status == 'draft')
                                    @can('official-travels.delete')
                                        <button type="button" class="btn-action delete-btn" data-toggle="modal"
                                            data-target="#deleteModal">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    @endcan

                                    <button type="button" class="btn-action submit-btn" data-toggle="modal"
                                        data-target="#submitModal">
                                        <i class="fas fa-paper-plane"></i> Submit for Approval
                                    </button>
                                @endif
                            @endif

                            @if ($officialtravel->status == 'approved')
                                @can('official-travels.stamp')
                                    @if ($officialtravel->canRecordArrival())
                                        <a href="{{ route('officialtravels.showArrivalForm', $officialtravel->id) }}"
                                            class="btn-action arrival-btn">
                                            <i class="fas fa-plane-arrival"></i> Record Arrival
                                        </a>
                                    @endif

                                    @if ($officialtravel->canRecordDeparture())
                                        <a href="{{ route('officialtravels.showDepartureForm', $officialtravel->id) }}"
                                            class="btn-action departure-btn">
                                            <i class="fas fa-plane-departure"></i> Record Departure
                                        </a>
                                    @endif

                                    @if ($officialtravel->canClose())
                                        <button type="button" class="btn-action close-btn" data-toggle="modal"
                                            data-target="#closeModal">
                                            <i class="fas fa-lock"></i> Close Official Travel
                                        </button>
                                    @endif
                                @endcan
                            @endif
                        @endif
                        <a href="{{ route('officialtravels.print', $officialtravel->id) }}" class="btn btn-primary"
                            target="_blank">
                            <i class="fas fa-print"></i> Print</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    @if ($officialtravel->status == 'draft')
        <div class="modal fade custom-modal" id="deleteModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Travel Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p class="delete-message">Are you sure you want to delete this official travel request?</p>
                        <p class="delete-warning">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('officialtravels.destroy', $officialtravel->id) }}" method="POST"
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

    <!-- Close Modal -->
    @if ($officialtravel->status == 'approved' && $officialtravel->canClose())
        <div class="modal fade custom-modal" id="closeModal" tabindex="-1" role="dialog"
            aria-labelledby="closeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="closeModalLabel">Close Travel Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="close-icon">
                            <i class="fas fa-lock text-warning"></i>
                        </div>
                        <p class="close-message">Are you sure you want to close this official travel?</p>
                        <p class="close-warning">This action cannot be undone and no further changes will be allowed.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('officialtravels.close', $officialtravel->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-confirm-close">Yes, Close Travel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('styles')
    <style>
        /* Custom Styles for Official Travel Detail */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .travel-header {
            position: relative;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .travel-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .travel-number {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .travel-destination {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .travel-date {
            font-size: 14px;
            opacity: 0.9;
        }

        .travel-status-pill {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .travel-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content Styles */
        .travel-content {
            padding: 0 20px;
        }

        /* Cards */
        .travel-card {
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

        /* Traveler Card */
        .traveler-card {
            overflow: hidden;
        }

        .traveler-header {
            padding: 20px;
            background-color: #2c3e50;
            color: white;
        }

        .traveler-name {
            font-size: 18px;
            margin: 0 0 4px;
            font-weight: 500;
        }

        .traveler-nik {
            font-size: 13px;
            opacity: 0.9;
        }

        .traveler-details {
            padding: 15px;
        }

        .traveler-detail-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .traveler-detail-item:last-child {
            border-bottom: none;
        }

        .detail-icon {
            color: #3498db;
            margin-right: 12px;
            font-size: 16px;
        }

        .detail-content {
            flex: 1;
        }





        /* Followers Card */
        .followers-card {
            overflow: hidden;
        }

        .followers-count {
            background: #3498db;
            color: white;
            font-size: 14px;
            border-radius: 4px;
            padding: 2px 8px;
            margin-left: 8px;
        }

        .followers-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .follower-item {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
        }

        .follower-name {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .follower-position {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .follower-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .follower-nik,
        .follower-department {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .follower-project {
            font-size: 14px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .follower-meta i,
        .follower-project i {
            font-size: 14px;
            width: 16px;
            text-align: center;
        }

        /* Action Buttons */
        .travel-action-buttons {
            display: grid;
            grid-template-columns: 1fr;
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
        }

        .back-btn {
            background-color: #64748b;
        }

        .back-btn:hover {
            color: white;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .edit-btn:hover {
            color: white;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            color: white;
        }

        .arrival-btn {
            background-color: #27ae60;
        }

        .arrival-btn:hover {
            color: white;
        }

        .departure-btn {
            background-color: #8e44ad;
        }

        .departure-btn:hover {
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
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

        .delete-icon {
            color: #e74c3c;
            margin-bottom: 15px;
        }

        .delete-message {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .delete-warning {
            font-size: 13px;
            color: #64748b;
        }

        .btn-cancel {
            padding: 8px 16px;
            border-radius: 4px;
            background: #e2e8f0;
            color: #475569;
            font-weight: 500;
        }

        .btn-confirm-delete {
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #e74c3c;
            color: white;
            font-weight: 500;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            /* Reorder columns on mobile */
            .travel-content .row {
                display: flex;
                flex-direction: column;
            }

            .travel-content .col-lg-8 {
                order: 1;
                width: 100%;
            }

            .travel-content .col-lg-4 {
                order: 2;
                width: 100%;
            }

            /* Ensure cards maintain proper spacing */
            .travel-card {
                margin-bottom: 20px;
            }

            /* Adjust padding for better mobile view */
            .travel-content {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .travel-header {
                height: auto;
                padding: 15px;
                position: relative;
            }

            .travel-header-content {
                padding-right: 80px;
                /* Create space for the status pill */
            }

            .travel-destination {
                font-size: 20px;
            }

            .travel-status-pill {
                position: absolute;
                top: 15px;
                right: 15px;
                margin-top: 0;
                align-self: flex-start;
            }

            /* Additional mobile-specific adjustments */
            .card-body {
                padding: 15px;
            }

            .info-item {
                padding: 10px 0;
            }

            .followers-list {
                max-height: 300px;
            }
        }

        /* Preserve desktop layout above 992px */
        @media (min-width: 993px) {
            .travel-content .row {
                display: flex;
                flex-wrap: wrap;
            }

            .travel-content .col-lg-8 {
                flex: 0 0 66.666667%;
                max-width: 66.666667%;
            }

            .travel-content .col-lg-4 {
                flex: 0 0 33.333333%;
                max-width: 33.333333%;
            }
        }


        .close-btn {
            background-color: #f1c40f;
            color: #2c3e50;
        }

        .close-btn:hover {
            color: #2c3e50;
        }

        .btn-confirm-close {
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #f1c40f;
            color: #2c3e50;
            font-weight: 500;
            border: none;
        }

        .close-icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 15px;
        }

        .close-message {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }

        .close-warning {
            font-size: 13px;
            color: #e74c3c;
            text-align: center;
        }

        .btn-action.submit-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-action.submit-btn:hover {
            background: linear-gradient(135deg, #218838, #1ea085);
        }

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

        /* Stops Timeline Styles */
        .stops-timeline-card {
            margin-bottom: 20px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 30px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -27px;
            top: 5px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 3px solid #e9ecef;
            z-index: 2;
        }

        .timeline-item.complete .timeline-marker {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .timeline-item.arrival-only .timeline-marker {
            background: #ffc107;
            border-color: #ffc107;
            color: white;
        }

        .timeline-item.departure-only .timeline-marker {
            background: #17a2b8;
            border-color: #17a2b8;
            color: white;
        }

        .timeline-content {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #e9ecef;
        }

        .timeline-item.complete .timeline-content {
            border-left-color: #28a745;
        }

        .timeline-item.arrival-only .timeline-content {
            border-left-color: #ffc107;
        }

        .timeline-item.departure-only .timeline-content {
            border-left-color: #17a2b8;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .timeline-header h4 {
            margin: 0;
            color: #333;
            font-size: 16px;
        }

        .timeline-status {
            font-size: 12px;
        }

        .timeline-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .timeline-detail-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .timeline-detail-item i {
            margin-top: 3px;
            font-size: 16px;
        }

        .timeline-detail-item .detail-content {
            flex: 1;
        }

        .timeline-detail-item .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .timeline-detail-item .detail-value {
            color: #333;
            font-size: 14px;
            line-height: 1.4;
        }

        .no-stops {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .no-stops i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        .no-stops p {
            margin: 0;
            font-size: 16px;
        }

        /* Accordion Styles */
        .accordion .card {
            border: none;
            box-shadow: none;
            background: transparent;
        }

        .accordion .card-header {
            background: transparent;
            border: none;
            padding: 0;
        }

        .accordion .btn-link {
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 6px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .accordion .btn-link:hover {
            color: #495057;
            background: #e9ecef;
            text-decoration: none;
        }

        .accordion .btn-link:focus {
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .accordion .btn-link.collapsed .accordion-icon {
            transform: rotate(0deg);
        }

        .accordion .btn-link:not(.collapsed) .accordion-icon {
            transform: rotate(180deg);
        }

        .accordion-icon {
            transition: transform 0.3s ease;
            font-size: 12px;
        }

        .accordion-title {
            font-size: 14px;
        }

        .accordion .card-body {
            padding: 15px 0 0 0;
            background: transparent;
        }

        .accordion .timeline-details {
            margin-top: 0;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .timeline {
                padding-left: 20px;
            }

            .timeline::before {
                left: 10px;
            }

            .timeline-item {
                padding-left: 20px;
            }

            .timeline-marker {
                left: -15px;
                width: 25px;
                height: 25px;
            }

            .timeline-content {
                padding: 15px;
            }

            .timeline-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .accordion .btn-link {
                padding: 8px 12px;
                font-size: 13px;
            }
        }
    </style>

    <!-- Submit for Approval Modal -->
    @if ($officialtravel->status == 'draft')
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
                            Are you sure you want to submit this Official Travel for approval?
                        </div>
                        <div class="submit-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            This action will submit the document to the approval workflow and cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('officialtravels.submit', $officialtravel->id) }}" method="POST"
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
@endsection
