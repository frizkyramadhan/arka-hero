@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">{{ $officialtravel->project->project_name }}</div>
                <h1 class="travel-destination">{{ $officialtravel->official_travel_number }}</h1>
                <div class="travel-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($officialtravel->official_travel_date)) }}
                </div>
                <div
                    class="travel-status-pill {{ $officialtravel->official_travel_status == 'draft' ? 'status-draft' : ($officialtravel->official_travel_status == 'open' ? 'status-open' : ($officialtravel->official_travel_status == 'canceled' ? 'status-canceled' : 'status-closed')) }}">
                    <i
                        class="fas {{ $officialtravel->official_travel_status == 'draft' ? 'fa-edit' : ($officialtravel->official_travel_status == 'open' ? 'fa-plane' : ($officialtravel->official_travel_status == 'canceled' ? 'fa-times-circle' : 'fa-check-circle')) }}"></i>
                    {{ ucfirst($officialtravel->official_travel_status) }}

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
                                        <div class="info-value">{{ $officialtravel->project->project_name }}</div>
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
                                        <div class="info-value">{{ $officialtravel->transportation->transportation_name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-hotel"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Accommodation</div>
                                        <div class="info-value">{{ $officialtravel->accommodation->accommodation_name }}
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
                                        {{ $officialtravel->traveler->employee->fullname }}
                                    </div>
                                </div>
                            </div>
                            <div class="traveler-detail-item">
                                <i class="fas fa-sitemap detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Title</div>
                                    <div class="detail-value">{{ $officialtravel->traveler->position->position_name }}
                                    </div>
                                </div>
                            </div>
                            <div class="traveler-detail-item">
                                <i class="fas fa-globe detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Business Unit</div>
                                    <div class="detail-value">
                                        {{ $officialtravel->traveler->project->project_code }} :
                                        {{ $officialtravel->traveler->project->project_name }}</div>
                                </div>
                            </div>
                            <div class="traveler-detail-item">
                                <i class="fas fa-building detail-icon"></i>
                                <div class="detail-content">
                                    <div class="detail-label">Division / Department</div>
                                    <div class="detail-value">
                                        {{ $officialtravel->traveler->position->department->department_name }}</div>
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
                                                <div class="follower-name">{{ $detail->follower->employee->fullname }}
                                                </div>
                                                <div class="follower-position">
                                                    {{ $detail->follower->position->position_name }}</div>
                                                <div class="follower-meta">
                                                    <span class="follower-nik"><i class="fas fa-id-card"></i>
                                                        {{ $detail->follower->nik }}</span>
                                                    <span class="follower-department"><i class="fas fa-sitemap"></i>
                                                        {{ $detail->follower->position->department->department_name }}</span>
                                                </div>
                                                <div class="follower-project">
                                                    <i class="fas fa-project-diagram"></i>
                                                    {{ $detail->follower->project->project_code }} :
                                                    {{ $detail->follower->project->project_name }}
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
                    <!-- Approval Process -->
                    <div class="travel-card approval-process-card">
                        <div class="card-head">
                            <h2><i class="fas fa-stream"></i> Approval Flow</h2>
                        </div>
                        <div class="card-body">
                            <div class="approval-flow">
                                <!-- Recommendation -->
                                <div class="approval-step">
                                    <div
                                        class="step-icon {{ $officialtravel->recommendation_status == 'approved'
                                            ? 'approved'
                                            : ($officialtravel->recommendation_status == 'rejected'
                                                ? 'rejected'
                                                : 'pending') }}">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="step-content">
                                        <div class="step-header">
                                            <h4>Recommendation</h4>
                                            <div class="step-status {{ $officialtravel->recommendation_status }}">
                                                {{ ucfirst($officialtravel->recommendation_status) }}
                                            </div>
                                        </div>
                                        <div class="step-details">
                                            <div class="step-person">
                                                <i class="fas fa-user"></i>
                                                {{ $officialtravel->recommender->name ?? 'Not assigned' }}
                                            </div>
                                            <div class="step-date">
                                                <i class="fas fa-calendar-check"></i>
                                                {{ $officialtravel->recommendation_date ? date('d M Y H:i', strtotime($officialtravel->recommendation_date)) : 'Pending' }}
                                            </div>
                                        </div>
                                        <div class="step-remark">
                                            <blockquote class="remark-text">
                                                {{ $officialtravel->recommendation_remark ?? 'Waiting for recommendation' }}
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approval -->
                                <div class="approval-step">
                                    <div
                                        class="step-icon {{ $officialtravel->approval_status == 'approved'
                                            ? 'approved'
                                            : ($officialtravel->approval_status == 'rejected'
                                                ? 'rejected'
                                                : 'pending') }}">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="step-content">
                                        <div class="step-header">
                                            <h4>Approval</h4>
                                            <div class="step-status {{ $officialtravel->approval_status }}">
                                                {{ ucfirst($officialtravel->approval_status) }}
                                            </div>
                                        </div>
                                        <div class="step-details">
                                            <div class="step-person">
                                                <i class="fas fa-user"></i>
                                                {{ $officialtravel->approver->name ?? 'Not assigned' }}
                                            </div>
                                            <div class="step-date">
                                                <i class="fas fa-calendar-check"></i>
                                                {{ $officialtravel->approval_date ? date('d M Y H:i', strtotime($officialtravel->approval_date)) : 'Pending' }}
                                            </div>
                                        </div>
                                        <div class="step-remark">
                                            <blockquote class="remark-text">
                                                {{ $officialtravel->approval_remark ?? 'Waiting for approval' }}
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>

                                @if ($officialtravel->official_travel_status != 'draft' && $officialtravel->official_travel_status != 'canceled')
                                    <!-- Travel Status -->
                                    <div class="approval-step">
                                        <div
                                            class="step-icon {{ $officialtravel->arrival_check_by ? 'approved' : 'pending' }}">
                                            <i class="fas fa-plane-arrival"></i>
                                        </div>
                                        <div class="step-content">
                                            <div class="step-header">
                                                <h4>Arrival Check</h4>
                                                <div
                                                    class="step-status {{ $officialtravel->arrival_check_by ? 'approved' : 'pending' }}">
                                                    {{ $officialtravel->arrival_check_by ? 'Completed' : 'Pending' }}
                                                </div>
                                            </div>
                                            <div class="step-details">
                                                <div class="step-person">
                                                    <i class="fas fa-user"></i>
                                                    {{ $officialtravel->arrivalChecker->name ?? 'Not stamped yet' }}
                                                </div>
                                                <div class="step-date">
                                                    <i class="fas fa-calendar-check"></i>
                                                    {{ $officialtravel->arrival_at_destination ? date('d M Y H:i', strtotime($officialtravel->arrival_at_destination)) : 'Not arrived' }}
                                                </div>
                                            </div>
                                            <div class="step-remark">
                                                <blockquote class="remark-text">
                                                    {{ $officialtravel->arrival_remark ?? 'No arrival information' }}
                                                </blockquote>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Departure -->
                                    <div class="approval-step">
                                        <div
                                            class="step-icon {{ $officialtravel->departure_check_by ? 'approved' : 'pending' }}">
                                            <i class="fas fa-plane-departure"></i>
                                        </div>
                                        <div class="step-content">
                                            <div class="step-header">
                                                <h4>Departure Check</h4>
                                                <div
                                                    class="step-status {{ $officialtravel->departure_check_by ? 'approved' : 'pending' }}">
                                                    {{ $officialtravel->departure_check_by ? 'Completed' : 'Pending' }}
                                                </div>
                                            </div>
                                            <div class="step-details">
                                                <div class="step-person">
                                                    <i class="fas fa-user"></i>
                                                    {{ $officialtravel->departureChecker->name ?? 'Not stamped yet' }}
                                                </div>
                                                <div class="step-date">
                                                    <i class="fas fa-calendar-check"></i>
                                                    {{ $officialtravel->departure_at_destination ? date('d M Y H:i', strtotime($officialtravel->departure_at_destination)) : 'Not departed' }}
                                                </div>
                                            </div>
                                            <div class="step-remark">
                                                <blockquote class="remark-text">
                                                    {{ $officialtravel->departure_remark ?? 'No departure information' }}
                                                </blockquote>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="travel-action-buttons">
                        <a href="{{ route('officialtravels.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>

                        @if ($officialtravel->official_travel_status != 'canceled')
                            @if ($officialtravel->official_travel_status == 'draft')
                                @can('official-travels.edit')
                                    <a href="{{ route('officialtravels.edit', $officialtravel->id) }}"
                                        class="btn-action edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endcan

                                @can('official-travels.delete')
                                    <button type="button" class="btn-action delete-btn" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                @endcan
                            @endif

                            @can('official-travels.recommend')
                                @if (Auth::id() == $officialtravel->recommendation_by && $officialtravel->approval_status == 'pending')
                                    <a href="{{ route('officialtravels.recommend', $officialtravel->id) }}"
                                        class="btn-action recommend-btn">
                                        <i
                                            class="fas fa-{{ $officialtravel->recommendation_status == 'pending' ? 'user-check' : 'edit' }}"></i>
                                        {{ $officialtravel->recommendation_status == 'pending' ? 'Recommend' : 'Edit Recommendation' }}
                                    </a>
                                @endif
                            @endcan

                            @can('official-travels.approve')
                                @if (Auth::id() == $officialtravel->approval_by &&
                                        $officialtravel->recommendation_status == 'approved' &&
                                        ($officialtravel->approval_status == 'pending' ||
                                            ($officialtravel->approval_status != 'pending' && !$officialtravel->arrival_at_destination)))
                                    <a href="{{ route('officialtravels.approve', $officialtravel->id) }}"
                                        class="btn-action approve-btn">
                                        <i
                                            class="fas fa-{{ $officialtravel->approval_status == 'pending' ? 'user-shield' : 'edit' }}"></i>
                                        {{ $officialtravel->approval_status == 'pending' ? 'Approve' : 'Edit Approval' }}
                                    </a>
                                @endif
                            @endcan

                            @if ($officialtravel->official_travel_status == 'open')
                                @can('official-travels.stamp')
                                    @if (!$officialtravel->arrival_check_by)
                                        <a href="{{ route('officialtravels.showArrivalForm', $officialtravel->id) }}"
                                            class="btn-action arrival-btn">
                                            <i class="fas fa-plane-arrival"></i> Arrival Stamp
                                        </a>
                                    @elseif(!$officialtravel->departure_check_by)
                                        <a href="{{ route('officialtravels.showDepartureForm', $officialtravel->id) }}"
                                            class="btn-action departure-btn">
                                            <i class="fas fa-plane-departure"></i> Departure Stamp
                                        </a>
                                    @endif

                                    @if ($officialtravel->arrival_check_by && $officialtravel->departure_check_by)
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
    @if ($officialtravel->official_travel_status == 'draft')
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
    @if (
        $officialtravel->official_travel_status == 'open' &&
            $officialtravel->arrival_check_by &&
            $officialtravel->departure_check_by)
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
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .status-draft {
            background-color: #f1c40f;
            color: #000000;
        }

        .status-open {
            background-color: #3498db;
            color: #ffffff;
        }

        .status-closed {
            background-color: #27ae60;
            color: #ffffff;
        }

        .status-canceled {
            background-color: #e74c3c;
            color: #ffffff;
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

        /* Approval Flow */
        .approval-process-card {
            margin-top: 0;
        }

        .approval-flow {
            position: relative;
            padding: 10px 0;
        }

        .approval-flow::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20px;
            width: 3px;
            background: #e0e0e0;
        }

        .approval-step {
            position: relative;
            padding-left: 60px;
            margin-bottom: 30px;
        }

        .approval-step:last-child {
            margin-bottom: 0;
        }

        .step-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            background: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .step-icon.approved {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .step-icon.rejected {
            background: linear-gradient(135deg, #fa709a 0%, #ff0844 100%);
        }

        .step-icon.pending {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }

        .step-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 15px;
        }

        .step-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .step-header h4 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .step-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .step-status.approved {
            background-color: #e6f9e6;
            color: #0f8c0f;
        }

        .step-status.rejected {
            background-color: #ffe6e6;
            color: #cc0000;
        }

        .step-status.pending {
            background-color: #fff4e6;
            color: #cc7a00;
        }

        .step-details {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #777;
            margin-bottom: 15px;
        }

        .step-person,
        .step-date {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .step-remark {
            padding-top: 10px;
            border-top: 1px dashed #eee;
        }

        .remark-text {
            margin: 0;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #555;
            border-left: 3px solid #6c757d;
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

        .recommend-btn {
            background-color: #f39c12;
        }

        .approve-btn {
            background-color: #16a085;
        }

        .recommend-btn:hover,
        .approve-btn:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
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
    </style>
@endsection
