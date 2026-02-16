@extends('layouts.main')

@section('title', $title ?? 'Letter of Guarantee Details')

@section('content')
    <div class="content-wrapper-custom">
        <div class="flight-request-header">
            <div class="flight-request-header-content">
                <div class="flight-request-project">Letter of Guarantee</div>
                <h1 class="flight-request-number">{{ $issuance->issued_number }}</h1>
                <div class="flight-request-date">
                    <i class="far fa-calendar-alt"></i>
                    {{ $issuance->issued_date ? $issuance->issued_date->format('d F Y') : '-' }}
                </div>
                @php
                    $statusMap = [
                        'pending' => ['label' => 'Pending', 'class' => 'badge badge-warning', 'icon' => 'fa-clock'],
                        'approved' => [
                            'label' => 'Approved',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                    ];
                    $status = $issuance->status ?? 'pending';
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
                    <!-- LG Information -->
                    <div class="flight-request-card employee-card">
                        <div class="card-head">
                            <h2><i class="fas fa-file-invoice"></i> LG Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="employee-info-table">
                                <div class="info-row">
                                    <div class="info-label"><strong>ISSUED NUMBER:</strong></div>
                                    <div class="info-value">{{ $issuance->issued_number }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>ISSUED DATE:</strong></div>
                                    <div class="info-value">
                                        {{ $issuance->issued_date ? $issuance->issued_date->format('d F Y') : '-' }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>LETTER NUMBER:</strong></div>
                                    <div class="info-value">{{ $issuance->letter_number ?? '-' }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>BUSINESS PARTNER:</strong></div>
                                    <div class="info-value">{{ $issuance->businessPartner->bp_name ?? '-' }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>ISSUED BY:</strong></div>
                                    <div class="info-value">{{ $issuance->issuedBy->name ?? '-' }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>TOTAL TICKETS:</strong></div>
                                    <div class="info-value">{{ $issuance->issuanceDetails->count() }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>TOTAL PRICE:</strong></div>
                                    <div class="info-value">Rp
                                        {{ number_format($issuance->total_ticket_price ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><strong>NOTES:</strong></div>
                                    <div class="info-value">
                                        @if ($issuance->notes)
                                            <pre class="info-notes-pre">{{ $issuance->notes }}</pre>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Details -->
                    <div class="flight-request-card flight-details-card">
                        <div class="card-head">
                            <h2><i class="fas fa-ticket-alt"></i> Ticket Details</h2>
                        </div>
                        <div class="card-body">
                            @if ($issuance->issuanceDetails->count() > 0)
                                <div class="ticket-cards2-list">
                                    @foreach ($issuance->issuanceDetails as $detail)
                                        <div class="ticket-detail-card2">
                                            <div class="ticket-detail-card2-col ticket-detail-card2-info">
                                                <div class="ticket-detail-card2-num">Tiket {{ $detail->ticket_order }}
                                                </div>
                                                <h5 class="ticket-detail-card2-name text-uppercase">
                                                    {{ $detail->resolved_passenger_name ?? '-' }}
                                                </h5>
                                                <dl class="ticket-detail-card2-dl">
                                                    <dt>Booking Code</dt>
                                                    <dd>{{ $detail->booking_code ?? '-' }}</dd>
                                                    <dt>Detail Reservasi</dt>
                                                    <dd>{{ $detail->detail_reservation ?? '-' }}</dd>
                                                </dl>
                                            </div>
                                            <div class="ticket-detail-card2-col ticket-detail-card2-amounts">
                                                <div class="ticket-detail-card2-amount-row">
                                                    <span class="ticket-detail-card2-label">Ticket Price</span>
                                                    <span class="ticket-detail-card2-val">Rp
                                                        {{ $detail->ticket_price ? number_format($detail->ticket_price, 0, ',', '.') : '-' }}</span>
                                                </div>
                                                <div class="ticket-detail-card2-amount-row">
                                                    <span class="ticket-detail-card2-label">Service Charge</span>
                                                    <span class="ticket-detail-card2-val">Rp
                                                        {{ $detail->service_charge ? number_format($detail->service_charge, 0, ',', '.') : '-' }}</span>
                                                </div>
                                                <div class="ticket-detail-card2-amount-row">
                                                    <span class="ticket-detail-card2-label">Service VAT</span>
                                                    <span class="ticket-detail-card2-val">Rp
                                                        {{ $detail->service_vat ? number_format($detail->service_vat, 0, ',', '.') : '-' }}</span>
                                                </div>
                                                <div
                                                    class="ticket-detail-card2-amount-row ticket-detail-card2-amount-highlight">
                                                    <span class="ticket-detail-card2-label">622 (Company)</span>
                                                    <span class="ticket-detail-card2-val">Rp
                                                        {{ $detail->company_amount ? number_format($detail->company_amount, 0, ',', '.') : '-' }}</span>
                                                </div>
                                                <div class="ticket-detail-card2-amount-row">
                                                    <span class="ticket-detail-card2-label">151 (Advance)</span>
                                                    <span class="ticket-detail-card2-val">Rp
                                                        {{ $detail->employee_amount ? number_format($detail->employee_amount, 0, ',', '.') : '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-flight-details">
                                    <i class="fas fa-ticket-alt"></i>
                                    <p>No ticket details</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Related Flight Request(s) -->
                    @if ($issuance->flightRequests->count() > 0)
                        <div class="flight-request-card issuances-card">
                            <div class="card-head">
                                <h2><i class="fas fa-plane"></i> Related Flight Request(s)</h2>
                            </div>
                            <div class="card-body">
                                <div class="issuances-list">
                                    @foreach ($issuance->flightRequests as $fr)
                                        <div class="issuance-item">
                                            <div class="issuance-header">
                                                <h5>{{ $fr->form_number ?? 'FR-' . $fr->id }}</h5>
                                                <a href="{{ route('flight-requests.show', $fr->id) }}"
                                                    class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </div>
                                            <div class="issuance-details">
                                                <div class="issuance-detail-item">
                                                    <i class="fas fa-user"></i>
                                                    <span><strong>Purpose:</strong>
                                                        {{ $fr->purpose_of_travel ?? '-' }}</span>
                                                </div>
                                                <div class="issuance-detail-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <span><strong>Requested:</strong>
                                                        {{ $fr->requested_at ? $fr->requested_at->format('d F Y') : '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Manual Approvers Card -->
                    @if (!empty($issuance->manual_approvers))
                        <div class="flight-request-card mb-4">
                            <div class="card-head">
                                <h2><i class="fas fa-users"></i> Selected Approvers</h2>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => $issuance->manual_approvers ?? [],
                                    'mode' => 'view',
                                    'documentType' => 'flight_request_issuance',
                                    'documentId' => $issuance->id,
                                ])
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
                                @can('flight-issuances.edit')
                                    @if (!$issuance->flightRequests->contains('status', 'completed'))
                                        <a href="{{ route('flight-issuances.edit', $issuance->id) }}"
                                            class="btn-action edit-btn">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endif
                                @endcan
                                <a href="{{ route('flight-issuances.print', $issuance->id) }}" class="btn btn-primary"
                                    target="_blank">
                                    <i class="fas fa-print"></i> Print
                                </a>
                                @can('flight-issuances.delete')
                                    <form action="{{ route('flight-issuances.destroy', $issuance->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this Letter of Guarantee? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-danger w-100">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                @endcan
                                <a href="{{ route('flight-issuances.index') }}" class="btn-action back-btn">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Same styles as Flight Request Show */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

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

        .flight-request-content {
            padding: 0 20px;
        }

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

        .employee-info-table .info-notes-pre {
            margin: 0;
            padding: 0;
            font-family: inherit;
            font-size: 13px;
            white-space: pre-wrap;
            word-wrap: break-word;
            background: transparent;
            border: none;
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

        /* Ticket Details - Kartu 2 Kolom */
        .ticket-cards2-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .ticket-detail-card2 {
            display: flex;
            flex-wrap: wrap;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }

        .ticket-detail-card2-col {
            flex: 1 1 280px;
            min-width: 0;
            padding: 1rem 1.25rem;
        }

        .ticket-detail-card2-info {
            background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
            border-right: 1px solid #e9ecef;
        }

        .ticket-detail-card2-num {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .ticket-detail-card2-name {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 0.75rem 0;
            line-height: 1.3;
        }

        .ticket-detail-card2-dl {
            margin: 0;
            font-size: 0.8125rem;
        }

        .ticket-detail-card2-dl dt {
            font-weight: 600;
            color: #6c757d;
            margin-top: 0.5rem;
            margin-bottom: 0.15rem;
        }

        .ticket-detail-card2-dl dt:first-child {
            margin-top: 0;
        }

        .ticket-detail-card2-dl dd {
            margin: 0;
            color: #333;
        }

        .ticket-detail-card2-amounts {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.15rem;
        }

        .ticket-detail-card2-amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.2rem 0;
            border-bottom: 1px dotted #e9ecef;
            font-size: 0.875rem;
        }

        .ticket-detail-card2-amount-row:last-child {
            border-bottom: none;
        }

        .ticket-detail-card2-label {
            color: #6c757d;
        }

        .ticket-detail-card2-val {
            font-weight: 600;
            text-align: right;
        }

        .ticket-detail-card2-amount-highlight .ticket-detail-card2-val {
            color: #0d6efd;
        }

        @media (max-width: 576px) {
            .ticket-detail-card2-info {
                border-right: none;
                border-bottom: 1px solid #e9ecef;
            }
        }

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

        .btn-action.btn-danger {
            background-color: #dc3545;
        }

        .btn-action.btn-danger:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        @media (max-width: 992px) {
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

            .flight-request-content {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .flight-request-header {
                height: auto;
                padding: 15px;
            }

            .flight-request-status-pill {
                position: absolute;
                top: 15px;
                right: 15px;
            }

            .flight-request-number {
                font-size: 20px;
            }

            .card-body {
                padding: 15px;
            }
        }
    </style>
@endsection
