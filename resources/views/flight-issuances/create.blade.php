@extends('layouts.main')

@section('title', $title ?? 'Create Letter of Guarantee')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title ?? 'Create Letter of Guarantee' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('flight-issuances.index') }}">Flight Issuances</a></li>
                        <li class="breadcrumb-item active">Create LG</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('flight-issuances.store') }}" id="issuanceForm">
                @csrf
                @if (isset($flightRequests))
                    @foreach ($flightRequests as $fr)
                        <input type="hidden" name="flight_request_ids[]" value="{{ $fr->id }}">
                    @endforeach
                @elseif (isset($flightRequest))
                    <input type="hidden" name="flight_request_ids[]" value="{{ $flightRequest->id }}">
                @endif

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <!-- Letter Number Selection Card (same style as Official Travel) -->
                        <div class="card card-info card-outline elevation-2">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-hashtag mr-2"></i>
                                    <strong>Letter Number</strong>
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                @include('components.smart-letter-number-selector', [
                                    'categoryCode' => 'FR',
                                    'required' => true,
                                ])
                            </div>
                        </div>

                        <!-- LG Information Card -->
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-invoice mr-2"></i>
                                    <strong>LG Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="issued_number">
                                                Issued Number <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                </div>
                                                <input type="text" name="issued_number" id="issued_number"
                                                    class="form-control alert-warning @error('issued_number') is-invalid @enderror"
                                                    value="{{ old('issued_number') }}" placeholder="FR0001/ARKA/LG/I/2026"
                                                    readonly required>
                                                @error('issued_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Issued number will be auto-generated when you select a letter number above
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="issued_date">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                Issued Date <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="issued_date" id="issued_date"
                                                    class="form-control @error('issued_date') is-invalid @enderror"
                                                    value="{{ old('issued_date', date('Y-m-d')) }}" required>
                                                @error('issued_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="business_partner_id">
                                                <i class="fas fa-building mr-1"></i>
                                                Business Partner
                                            </label>
                                            <select name="business_partner_id" id="business_partner_id"
                                                class="form-control select2bs4 @error('business_partner_id') is-invalid @enderror"
                                                style="width: 100%;">
                                                <option value="">Select Business Partner</option>
                                                @foreach ($businessPartners as $bp)
                                                    <option value="{{ $bp->id }}"
                                                        {{ old('business_partner_id') == $bp->id ? 'selected' : '' }}>
                                                        {{ $bp->bp_code }} - {{ $bp->bp_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('business_partner_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="notes">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                Notes
                                            </label>
                                            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Optional notes">{{ old('notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Details Card -->
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-ticket-alt mr-2"></i>
                                    <strong>Ticket Details</strong>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="addTicketDetail">
                                        <i class="fas fa-plus"></i> Add Ticket
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="ticketDetailsContainer">
                                    @if (old('details'))
                                        @foreach (old('details') as $idx => $d)
                                            <div class="ticket-detail-item border p-3 mb-3"
                                                data-index="{{ $idx }}">
                                                <div class="row">
                                                    <div class="col-md-12 mb-2">
                                                        <strong>Ticket {{ $idx + 1 }}</strong>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger float-right remove-ticket"><i
                                                                class="fas fa-times"></i></button>
                                                    </div>
                                                    <input type="hidden"
                                                        name="details[{{ $idx }}][ticket_order]"
                                                        value="{{ $d['ticket_order'] ?? $idx + 1 }}">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-user mr-1"></i> Passenger Name <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text"
                                                                name="details[{{ $idx }}][passenger_name]"
                                                                class="form-control" required
                                                                value="{{ $d['passenger_name'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-barcode mr-1"></i> Booking Code</label>
                                                            <input type="text"
                                                                name="details[{{ $idx }}][booking_code]"
                                                                class="form-control"
                                                                value="{{ $d['booking_code'] ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-info-circle mr-1"></i> Detail
                                                                Reservation</label>
                                                            <textarea name="details[{{ $idx }}][detail_reservation]" class="form-control" rows="1">{{ $d['detail_reservation'] ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-money-bill-wave mr-1"></i> Ticket
                                                                Price</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend"><span
                                                                        class="input-group-text">Rp</span></div>
                                                                <input type="text"
                                                                    name="details[{{ $idx }}][ticket_price]"
                                                                    class="form-control amount-input" inputmode="decimal"
                                                                    placeholder="0"
                                                                    value="{{ isset($d['ticket_price']) && $d['ticket_price'] !== '' ? format_amount_id($d['ticket_price']) : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-receipt mr-1"></i> Service
                                                                Charge</label>
                                                            <div class="input-group optional-amount-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><input type="checkbox"
                                                                            class="optional-amount-check"
                                                                            {{ !empty($d['service_charge']) ? 'checked' : '' }}></span>
                                                                </div>
                                                                <input type="text"
                                                                    name="details[{{ $idx }}][service_charge]"
                                                                    class="form-control amount-input optional-amount-input"
                                                                    inputmode="decimal" placeholder="Rp"
                                                                    value="{{ !empty($d['service_charge']) ? format_amount_id($d['service_charge']) : '' }}"
                                                                    {{ !empty($d['service_charge']) ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-percent mr-1"></i> Service VAT</label>
                                                            <div class="input-group optional-amount-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><input type="checkbox"
                                                                            class="optional-amount-check"
                                                                            {{ !empty($d['service_vat']) ? 'checked' : '' }}></span>
                                                                </div>
                                                                <input type="text"
                                                                    name="details[{{ $idx }}][service_vat]"
                                                                    class="form-control amount-input optional-amount-input"
                                                                    inputmode="decimal" placeholder="Rp"
                                                                    value="{{ !empty($d['service_vat']) ? format_amount_id($d['service_vat']) : '' }}"
                                                                    {{ !empty($d['service_vat']) ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-user mr-1"></i> 151 (Advance)</label>
                                                            <div class="input-group optional-amount-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><input type="checkbox"
                                                                            class="optional-amount-check"
                                                                            {{ !empty($d['employee_amount']) ? 'checked' : '' }}></span>
                                                                </div>
                                                                <input type="text"
                                                                    name="details[{{ $idx }}][employee_amount]"
                                                                    class="form-control amount-input optional-amount-input"
                                                                    inputmode="decimal" placeholder="Rp"
                                                                    value="{{ !empty($d['employee_amount']) ? format_amount_id($d['employee_amount']) : '' }}"
                                                                    {{ !empty($d['employee_amount']) ? '' : 'disabled' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label><i class="fas fa-building mr-1"></i> 622
                                                                (Company)</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend"><span
                                                                        class="input-group-text">Rp</span></div>
                                                                <input type="text"
                                                                    name="details[{{ $idx }}][company_amount]"
                                                                    class="form-control amount-input company-amount-input"
                                                                    inputmode="decimal" readonly
                                                                    value="{{ !empty($d['company_amount']) ? format_amount_id($d['company_amount']) : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-4">
                        <!-- Flight Request Information Card -->
                        @php
                            $flightRequestsList = isset($flightRequests)
                                ? $flightRequests
                                : (isset($flightRequest)
                                    ? collect([$flightRequest])
                                    : collect());
                        @endphp
                        @foreach ($flightRequestsList as $frIndex => $flightRequest)
                            <div
                                class="card card-info card-outline elevation-3 compact-fr-card {{ $frIndex > 0 ? 'mt-3' : '' }}">
                                <div class="card-header py-2">
                                    <h3 class="card-title mb-0" style="font-size: 1rem;">
                                        <i class="fas fa-plane mr-1"></i>
                                        <strong>Flight
                                            Request{{ $flightRequestsList->count() > 1 ? ' #' . ($frIndex + 1) : '' }}</strong>
                                        @if ($flightRequestsList->count() > 1)
                                            <span
                                                class="badge badge-info badge-sm ml-1">{{ $flightRequest->form_number ?? 'FR-' . $flightRequest->id }}</span>
                                        @endif
                                    </h3>
                                </div>
                                <div class="card-body py-2">
                                    @php
                                        $employee = $flightRequest->employee;
                                        $administration =
                                            $flightRequest->administration ??
                                            ($employee ? $employee->activeAdministration : null);
                                        $name =
                                            $flightRequest->employee_name ?? ($employee ? $employee->fullname : 'N/A');
                                        $nik = $flightRequest->nik ?? ($administration ? $administration->nik : 'N/A');
                                    @endphp

                                    <div class="mb-2">
                                        <table class="table table-sm table-borderless mb-0 compact-table">
                                            <tr>
                                                <th class="compact-th">FR Number</th>
                                                <td class="compact-td">
                                                    <strong>{{ $flightRequest->form_number ?? '-' }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="compact-th">Name / NIK</th>
                                                <td class="compact-td">{{ strtoupper($name) }} / {{ $nik }}</td>
                                            </tr>
                                            <tr>
                                                <th class="compact-th">Purpose</th>
                                                <td class="compact-td">{{ $flightRequest->purpose_of_travel }}</td>
                                            </tr>
                                            <tr>
                                                <th class="compact-th">Travel Days</th>
                                                <td class="compact-td">{{ $flightRequest->total_travel_days ?? '-' }} days
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="border-top pt-2 mt-2">
                                        <h6 class="mb-1" style="font-size: 0.875rem; font-weight: 600;">
                                            <i class="fas fa-route mr-1"></i> Flight Details
                                        </h6>
                                        @php
                                            $orderedFlightDetails = $flightRequest->details
                                                ->sortBy(['segment_order', 'flight_date'])
                                                ->values();
                                        @endphp
                                        @if ($orderedFlightDetails->count() > 0)
                                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered mb-0 compact-flight-table">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th class="text-center"
                                                                style="width: 8%; font-size: 0.75rem; padding: 4px;">#</th>
                                                            <th style="font-size: 0.75rem; padding: 4px;">Route</th>
                                                            <th style="font-size: 0.75rem; padding: 4px;">Date</th>
                                                            <th style="font-size: 0.75rem; padding: 4px;">Time</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($orderedFlightDetails as $index => $detail)
                                                            <tr>
                                                                <td class="text-center"
                                                                    style="font-size: 0.8rem; padding: 4px;">
                                                                    {{ $index + 1 }}</td>
                                                                <td style="font-size: 0.8rem; padding: 4px;">
                                                                    <strong>{{ $detail->departure_city }}</strong> →
                                                                    {{ $detail->arrival_city }}
                                                                </td>
                                                                <td style="font-size: 0.8rem; padding: 4px;">
                                                                    {{ $detail->flight_date ? $detail->flight_date->format('d M Y') : '-' }}
                                                                </td>
                                                                <td style="font-size: 0.8rem; padding: 4px;">
                                                                    {{ $detail->flight_time ? \Carbon\Carbon::parse($detail->flight_time)->format('H:i') : '-' }}
                                                                    @if ($detail->airline)
                                                                        <br><small
                                                                            class="text-muted">{{ $detail->airline }}</small>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted mb-0" style="font-size: 0.8rem;">No flight details
                                                available.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Manual Approvers -->
                        <div class="card card-warning card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-check mr-2"></i>
                                    <strong>Approver Selection</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                @include('components.manual-approver-selector', [
                                    'name' => 'manual_approvers',
                                    'documentType' => 'flight_request_issuance',
                                    'selectedApprovers' => old('manual_approvers', []),
                                ])
                            </div>
                        </div>

                        <!-- Action Buttons Card -->
                        <div class="card elevation-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-save mr-2"></i> Create LG
                                </button>
                                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times-circle mr-2"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .letter-number-selector .form-label {
            font-weight: 600;
        }

        .letter-number-selector .btn-group-vertical .btn {
            width: 100%;
        }

        /* Issued Number Styling (same as Official Travel LOT Number) */
        #issued_number.alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        #issued_number.alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        /* Nominal inputs: rata kanan + format separator */
        .amount-input {
            text-align: right;
        }

        /* Compact Flight Request Information Card */
        .compact-fr-card .card-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-bottom: 2px solid #0f6674;
        }

        .compact-fr-card .card-title {
            color: white;
        }

        .compact-fr-card .badge-sm {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        .compact-table {
            margin-bottom: 0;
        }

        .compact-table .compact-th {
            width: 35%;
            font-size: 0.8rem;
            font-weight: 600;
            color: #495057;
            padding: 0.25rem 0.5rem;
            vertical-align: middle;
            border: none;
        }

        .compact-table .compact-td {
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
            vertical-align: middle;
            border: none;
            color: #212529;
        }

        .compact-flight-table {
            font-size: 0.8rem;
            margin-bottom: 0;
        }

        .compact-flight-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .compact-flight-table tbody tr {
            transition: background-color 0.2s;
        }

        .compact-flight-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .compact-flight-table tbody td {
            border-color: #e9ecef;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/amount-format.js') }}"></script>
    <script>
        let ticketIndex = 0;

        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            ticketIndex = $('#ticketDetailsContainer .ticket-detail-item').length;
            if (ticketIndex === 0) {
                addTicketDetail();
            }
            $('#addTicketDetail').click(function() {
                addTicketDetail();
            });

            // Update Issued Number when letter number or issued date changes
            updateIssuedNumberDisplay();

            @if (old('letter_number_id'))
                setTimeout(function() {
                    const oldLetterNumberId = '{{ old('letter_number_id') }}';
                    if (oldLetterNumberId) {
                        $('[name="letter_number_id"]').val(oldLetterNumberId).trigger('change');
                    }
                }, 1000);
            @endif
        });

        function updateIssuedNumberDisplay() {
            // Helper to convert month (1-12) to Roman numeral
            function monthToRoman(monthNumber) {
                const romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                if (monthNumber < 1 || monthNumber > 12) return '';
                return romans[monthNumber - 1];
            }

            function recomputeIssuedNumber() {
                const $select = $('[name="letter_number_id"]');
                const selectedOption = $select.find('option:selected');

                // Get month/year from issued_date (or today if empty/invalid)
                let dateStr = $('#issued_date').val();
                let jsDate = dateStr ? new Date(dateStr) : new Date();
                if (isNaN(jsDate.getTime())) {
                    jsDate = new Date();
                }

                const month = jsDate.getMonth() + 1; // 1-12
                const year = jsDate.getFullYear();
                const romanMonth = monthToRoman(month);

                if (selectedOption.val() && selectedOption.text()) {
                    // Extract letter number from option text (same pattern as Official Travel)
                    const letterNumber = selectedOption.text().split(' - ')[0];

                    if (letterNumber) {
                        // Generate issued number with selected letter number
                        const formattedIssuedNumber = `${letterNumber}/ARKA/LG/${romanMonth}/${year}`;

                        $('#issued_number').val(formattedIssuedNumber);

                        // Visual feedback
                        $('#issued_number').addClass('alert-success').removeClass('alert-warning');
                    } else {
                        // Reset to placeholder if no letter number found
                        const defaultIssued = `[Letter Number]/ARKA/LG/${romanMonth}/${year}`;
                        $('#issued_number').val(defaultIssued);
                        $('#issued_number').addClass('alert-warning').removeClass('alert-success');
                    }
                } else {
                    // Reset to placeholder if no letter number selected
                    const defaultIssued = `[Letter Number]/ARKA/LG/${romanMonth}/${year}`;
                    $('#issued_number').val(defaultIssued);
                    $('#issued_number').addClass('alert-warning').removeClass('alert-success');
                }
            }

            // Recompute when letter number or issued date changes
            $(document).on('change', '[name=\"letter_number_id\"]', recomputeIssuedNumber);
            $('#issued_date').on('change', recomputeIssuedNumber);
        }

        function addTicketDetail() {
            const html = `
                <div class="ticket-detail-item border p-3 mb-3" data-index="${ticketIndex}">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong>Ticket ${ticketIndex + 1}</strong>
                            <button type="button" class="btn btn-sm btn-danger float-right remove-ticket">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="hidden" name="details[${ticketIndex}][ticket_order]" value="${ticketIndex + 1}">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-user mr-1"></i> Passenger Name <span class="text-danger">*</span></label>
                                <input type="text" name="details[${ticketIndex}][passenger_name]" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-barcode mr-1"></i> Booking Code</label>
                                <input type="text" name="details[${ticketIndex}][booking_code]" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-info-circle mr-1"></i> Detail Reservation</label>
                                <textarea name="details[${ticketIndex}][detail_reservation]" class="form-control" rows="1"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-money-bill-wave mr-1"></i> Ticket Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" name="details[${ticketIndex}][ticket_price]" class="form-control amount-input" inputmode="decimal" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label><i class="fas fa-receipt mr-1"></i> Service Charge</label>
                                <div class="input-group optional-amount-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <input type="checkbox" class="optional-amount-check">
                                        </span>
                                    </div>
                                    <input type="text" name="details[${ticketIndex}][service_charge]" class="form-control amount-input optional-amount-input" inputmode="decimal" placeholder="Rp" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label><i class="fas fa-percent mr-1"></i> Service VAT</label>
                                <div class="input-group optional-amount-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <input type="checkbox" class="optional-amount-check">
                                        </span>
                                    </div>
                                    <input type="text" name="details[${ticketIndex}][service_vat]" class="form-control amount-input optional-amount-input" inputmode="decimal" placeholder="Rp" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label><i class="fas fa-user mr-1"></i> 151 (Advance)</label>
                                <div class="input-group optional-amount-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <input type="checkbox" class="optional-amount-check">
                                        </span>
                                    </div>
                                    <input type="text" name="details[${ticketIndex}][employee_amount]" class="form-control amount-input optional-amount-input" inputmode="decimal" placeholder="Rp" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label><i class="fas fa-building mr-1"></i> 622 (Company)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" name="details[${ticketIndex}][company_amount]" class="form-control amount-input company-amount-input" inputmode="decimal" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#ticketDetailsContainer').append(html);
            updateRowCompanyAmount($('#ticketDetailsContainer').children('.ticket-detail-item').last());
            ticketIndex++;
        }

        $(document).on('click', '.remove-ticket', function() {
            $(this).closest('.ticket-detail-item').remove();
            $('.ticket-detail-item').each(function(index) {
                $(this).find('input[name*="[ticket_order]"]').val(index + 1);
                $(this).find('strong').first().text('Ticket ' + (index + 1));
            });
            ticketIndex = $('.ticket-detail-item').length;
        });

        // Service VAT = 11% dari Service Charge saat checkbox dicentang
        function updateRowServiceVatFromCharge($row) {
            var $vatCheck = $row.find('input[name*="[service_vat]"]').closest('.optional-amount-group').find(
                '.optional-amount-check');
            var $vatInput = $row.find('input[name*="[service_vat]"]');
            if ($vatCheck.is(':checked')) {
                var charge = AmountFormat.parse($row.find('input[name*="[service_charge]"]').val());
                $vatInput.val(AmountFormat.format(charge * 0.11)).prop('disabled', false);
            }
        }
        // 622 (Company) = (ticket price + service charge + service VAT) - 151 (Advance)
        function updateRowCompanyAmount($row) {
            var ticket = AmountFormat.parse($row.find('input[name*="[ticket_price]"]').val());
            var serviceCharge = AmountFormat.parse($row.find('input[name*="[service_charge]"]').val());
            var serviceVat = AmountFormat.parse($row.find('input[name*="[service_vat]"]').val());
            var advance = AmountFormat.parse($row.find('input[name*="[employee_amount]"]').val());
            var company = (ticket + serviceCharge + serviceVat) - advance;
            $row.find('input.company-amount-input').val(AmountFormat.format(company));
        }
        $(document).on('input change',
            '.ticket-detail-item input[name*="[ticket_price]"], .ticket-detail-item input[name*="[service_charge]"], .ticket-detail-item input[name*="[service_vat]"], .ticket-detail-item input[name*="[employee_amount]"]',
            function() {
                var $row = $(this).closest('.ticket-detail-item');
                if ($(this).attr('name') && $(this).attr('name').indexOf('[service_charge]') >= 0) {
                    updateRowServiceVatFromCharge($row);
                }
                updateRowCompanyAmount($row);
            });
        $(document).on('change', '.optional-amount-check', function() {
            var $group = $(this).closest('.optional-amount-group');
            var $input = $group.find('.optional-amount-input');
            if ($(this).is(':checked')) {
                $input.prop('disabled', false);
                if ($input.attr('name') && $input.attr('name').indexOf('[service_vat]') >= 0) {
                    updateRowServiceVatFromCharge($(this).closest('.ticket-detail-item'));
                }
            } else {
                $input.prop('disabled', true).val('');
            }
            updateRowCompanyAmount($(this).closest('.ticket-detail-item'));
        });

        $(document).on('input', '.amount-input', function() {
            var $el = $(this);
            if ($el.prop('readonly')) return;
            var fmt = AmountFormat.formatTyping($el.val());
            if ($el.val() !== fmt) {
                $el.val(fmt);
                var e = $el[0];
                if (e.setSelectionRange) e.setSelectionRange(fmt.length, fmt.length);
            }
        });
        $(document).on('blur', '.amount-input', function() {
            var $el = $(this);
            if (!$el.prop('readonly')) {
                var v = AmountFormat.parse($el.val());
                $el.val(v ? AmountFormat.format(v) : '');
            }
        });
        $('#issuanceForm').on('submit', function() {
            $('.amount-input').each(function() {
                var v = AmountFormat.parse($(this).val());
                $(this).val(v ? String(v) : '');
            });
        });

        // Optional amount: enable inputs whose checkbox is checked (for edit or old input)
        $(document).ready(function() {
            $('.optional-amount-check:checked').each(function() {
                $(this).closest('.optional-amount-group').find('.optional-amount-input').prop('disabled',
                    false);
            });
            $('.ticket-detail-item').each(function() {
                updateRowCompanyAmount($(this));
            });
        });
    </script>
@endsection
