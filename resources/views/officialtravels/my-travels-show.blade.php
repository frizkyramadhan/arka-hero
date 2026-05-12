@extends('layouts.main')

@section('title', 'My Official Travel Details')

@section('content')
    <div class="content-wrapper-custom">
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">{{ $officialtravel->project->project_name ?? 'No Project' }}</div>
                <h1 class="travel-destination">
                    {{ $officialtravel->letter_number ? $officialtravel->official_travel_number : 'LOT Draft : ' . $officialtravel->official_travel_number }}
                </h1>
                <div class="travel-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($officialtravel->official_travel_date)) }}
                </div>
                @php
                    $statusMap = [
                        'draft' => ['label' => 'Draft', 'class' => 'badge badge-secondary', 'icon' => 'fa-edit'],
                        'pending_hr' => [
                            'label' => 'Menunggu Konfirmasi HR',
                            'class' => 'badge badge-warning',
                            'icon' => 'fa-clock',
                        ],
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
                        : $statusMap[$status] ?? [
                                'label' => ucfirst($status),
                                'class' => 'badge badge-secondary',
                                'icon' => 'fa-question-circle',
                            ];
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
                            @include('officialtravels.partials.travel-details-info-grid', [
                                'officialtravel' => $officialtravel,
                            ])
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
                    @include('officialtravels.partials.stops-timeline-card')

                    @include('officialtravels.partials.flight-request-info')

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

                    <!-- Action Buttons -->
                    <div class="travel-action-buttons">
                        <a href="{{ route('officialtravels.my-travels') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        @if (
                            $officialtravel->traveler_id === auth()->user()->administration_id &&
                                $officialtravel->submitted_by_user &&
                                empty($officialtravel->letter_number_id))
                            <a href="{{ route('officialtravels.my-travels.edit', $officialtravel->id) }}"
                                class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        @include('officialtravels.partials.print-split-button', [
                            'officialtravel' => $officialtravel,
                            'label' => 'Print',
                            'btnClass' => 'btn-primary',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
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

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

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
        }

        @media (max-width: 768px) {
            .travel-header {
                height: auto;
                padding: 15px;
                position: relative;
            }

            .travel-header-content {
                padding-right: 80px;
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
        }
    </style>
@endsection
