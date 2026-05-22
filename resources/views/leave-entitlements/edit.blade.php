@extends('layouts.main')

@section('title', ($isEditMode ?? false ? 'Edit' : 'Tambah') . ' Hak Cuti ' . ($scopeTitle ?? 'Tahunan') . ' - ' .
    $employee->fullname)

@section('content')
    @php
        // Get active administration (is_active = 1)
        $activeAdministration = $employee->administrations->where('is_active', 1)->first();

        // If no active administration, use the last one
        if (!$activeAdministration) {
            $activeAdministration = $employee->administrations->sortByDesc('doh')->first();
        }

        // Calculate years of service based on termination reason logic:
        // - If termination_reason = "end of contract" → count from first DOH (continuity)
        // - If termination_reason != "end of contract" → count from DOH after termination (reset)
        $allAdministrations = $employee->administrations->whereNotNull('doh')->sortBy('doh')->values();

        $serviceStartDoh = null;
        $serviceStartNik = null;

        if ($allAdministrations->count() > 0) {
            // Start with first DOH (earliest)
            $serviceStartDoh = $allAdministrations->first()->doh;
            $serviceStartNik = $allAdministrations->first()->nik;

            // Check each administration for termination reason
            foreach ($allAdministrations as $admin) {
                if ($admin->termination_date && $admin->termination_reason) {
                    $terminationReason = strtolower(trim($admin->termination_reason));

                    // If termination is NOT "end of contract", reset to next DOH
                    if ($terminationReason !== 'end of contract') {
                        // Find next administration after this termination
                        $nextAdmin = $allAdministrations->where('doh', '>', $admin->termination_date)->first();
                        if ($nextAdmin) {
                            $serviceStartDoh = $nextAdmin->doh;
                            $serviceStartNik = $nextAdmin->nik;
                        }
                    }
                }
            }
        }
    @endphp

    @if ($businessRules)
        @php
            $lslPeriodDatesByLeaveTypeId = $lslPeriodDatesByLeaveTypeId ?? [];
            $activeLslPeriod = collect($lslPeriodDatesByLeaveTypeId)->first();
            $entitlementScope = $entitlementScope ?? 'annual';
            $isLslScope = $entitlementScope === 'lsl';
            $bannerPeriod =
                ($isEditMode ?? false) && ($periodDates ?? null)
                    ? $periodDates
                    : ($isLslScope && $activeLslPeriod
                        ? $activeLslPeriod
                        : $periodDates ?? null);
            $periodLabel = $isLslScope ? 'Periode Cuti Panjang' : 'Periode Tahunan';
        @endphp
        <!-- Info Banner - Full Width -->
        <div class="info-banner {{ $isLslScope ? 'info-banner--lsl' : 'info-banner--annual' }}">
            <div class="info-banner-content">
                <div class="employee-info">
                    <div class="employee-name">{{ $employee->fullname }}</div>
                    <div class="employee-details">
                        {{ $activeAdministration->project->project_code ?? 'N/A' }} -
                        {{ $activeAdministration->nik ?? 'N/A' }}
                    </div>
                    @if ($bannerPeriod)
                        <x-leave-entitlement-period :start="$bannerPeriod['start']" :end="$bannerPeriod['end']" :category="$isLslScope ? 'lsl' : null" variant="banner" />
                    @endif
                </div>
                <div class="period-year">
                    @if ($isEditMode ?? false)
                        <i class="fas fa-edit mr-1"></i>{{ $periodLabel }}
                    @else
                        + {{ $periodLabel }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    <section class="content">
        <div class="container-fluid">
            @if ($businessRules)
                @if ($isLslScope)
                    <p class="text-muted small mb-3">
                        <i class="fas fa-hourglass-half text-warning"></i> Form cuti panjang — periode 5/6 tahun, saldo
                        default 50 hari.
                    </p>
                @else
                    <p class="text-muted small mb-3">
                        <i class="fas fa-calendar-alt text-info"></i> Form cuti tahunan — periode 1 tahun (tahunan,
                        berbayar, tanpa upah).
                    </p>
                @endif

                <div class="row">
                    <!-- Sidebar: Employee Info -->
                    <div class="col-md-3">
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-info-circle"></i> Employee Info</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="info-sidebar">
                                    <div class="info-section">
                                        <div class="info-label"><i class="fas fa-briefcase text-primary"></i> Level</div>
                                        <div class="info-value">
                                            {{ $activeAdministration->level->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="info-section">
                                        <div class="info-label"><i class="fas fa-user-tag text-secondary"></i> Position
                                        </div>
                                        <div class="info-value">
                                            {{ $activeAdministration->position->position_name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="info-section">
                                        <div class="info-label"><i class="fas fa-calendar-check text-success"></i> DOH</div>
                                        <div class="info-value">
                                            @if ($serviceStartDoh)
                                                {{ \Carbon\Carbon::parse($serviceStartDoh)->format('d M Y') }}
                                                @if ($serviceStartNik && $activeAdministration && $serviceStartNik != $activeAdministration->nik)
                                                    <br><small class="text-info"><i class="fas fa-info-circle"></i> From
                                                        NIK: {{ $serviceStartNik }}</small>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                                    <div class="info-section">
                                        <div class="info-label"><i class="fas fa-clock text-warning"></i> Service</div>
                                        <div class="info-value">
                                            <strong>{{ $businessRules['years_of_service'] }} years</strong>
                                            <small class="text-muted d-block">({{ $businessRules['months_of_service'] }}
                                                months)</small>
                                        </div>
                                    </div>
                                    <div class="info-section">
                                        <div class="info-label"><i class="fas fa-user-tag text-info"></i> Staff Type</div>
                                        <div class="info-value">
                                            <span
                                                class="badge badge-{{ $businessRules['staff_type'] === 'Staff' ? 'info' : 'secondary' }}">{{ $businessRules['staff_type'] }}</span>
                                        </div>
                                    </div>
                                    @if (!empty($businessRules['special_notes']))
                                        <div class="info-section border-top pt-3 mt-2">
                                            <div class="info-label"><i class="fas fa-exclamation-triangle text-warning"></i>
                                                Special Rules</div>
                                            <div class="info-value">
                                                <ul class="mb-0 pl-3" style="font-size: 0.85rem;">
                                                    @foreach ($businessRules['special_notes'] as $note)
                                                        <li>{{ $note }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content: Form -->
                    <div class="col-md-9">
                        <!-- Leave Entitlements Form Card -->
                        <form method="POST" action="{{ route('leave.entitlements.employee.update', $employee->id) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="entitlement_scope" value="{{ $entitlementScope }}">

                            <!-- Hidden fields for period dates -->
                            @if ($periodDates)
                                <input type="hidden" name="period_start"
                                    value="{{ $periodDates['start']->format('Y-m-d') }}">
                                <input type="hidden" name="period_end" value="{{ $periodDates['end']->format('Y-m-d') }}">
                            @endif

                            @php
                                $groupedLeavesForDisplay = collect($businessRules['eligible_leaves'])->groupBy(
                                    'category',
                                );
                                $globalIndex = 0;
                            @endphp

                            <!-- Tab Navigation / single scope form -->
                            <div class="card card-primary card-outline {{ $isLslScope ? 'card-warning' : '' }}">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-{{ $isLslScope ? 'hourglass-half' : 'calendar-alt' }} mr-2"></i>
                                        {{ $isEditMode ?? false ? 'Edit' : 'Tambah' }} Hak Cuti
                                        {{ $scopeTitle ?? 'Tahunan' }}
                                    </h3>
                                </div>
                                @if ($periodDates)
                                    <div class="card-body pb-0">
                                        <x-leave-entitlement-period :start="$periodDates['start']" :end="$periodDates['end']" :category="$isLslScope ? 'lsl' : null"
                                            variant="panel" />
                                    </div>
                                @endif
                                <div class="card-body pt-2">
                                    <div class="tab-content" id="leaveTabsContent">
                                        @foreach ($groupedLeavesForDisplay as $cat => $catLeaves)
                                            @php
                                                $catId = str_replace(' ', '', strtolower($cat));
                                                $isActive = true;
                                            @endphp
                                            <div class="tab-pane fade show active" id="{{ $catId }}"
                                                role="tabpanel">
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th width="40%">Leave Type</th>
                                                                <th width="25%" class="text-center">Default</th>
                                                                <th width="25%" class="text-center">Entitlement (Days)
                                                                </th>
                                                                <th width="10%" class="text-center">Used</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($catLeaves as $leave)
                                                                @php
                                                                    $rowPeriodDates = $periodDates;
                                                                    if (
                                                                        !($isEditMode ?? false) &&
                                                                        strtolower($cat) === 'lsl' &&
                                                                        !empty(
                                                                            $lslPeriodDatesByLeaveTypeId[
                                                                                $leave['id'] ?? null
                                                                            ] ?? null
                                                                        )
                                                                    ) {
                                                                        $rowPeriodDates =
                                                                            $lslPeriodDatesByLeaveTypeId[$leave['id']];
                                                                    }

                                                                    $existingEntitlement = null;
                                                                    if ($rowPeriodDates) {
                                                                        $existingEntitlement = \App\Models\LeaveEntitlement::where(
                                                                            'employee_id',
                                                                            $employee->id,
                                                                        )
                                                                            ->where('leave_type_id', $leave['id'])
                                                                            ->whereDate(
                                                                                'period_start',
                                                                                $rowPeriodDates['start']->format(
                                                                                    'Y-m-d',
                                                                                ),
                                                                            )
                                                                            ->whereDate(
                                                                                'period_end',
                                                                                $rowPeriodDates['end']->format('Y-m-d'),
                                                                            )
                                                                            ->first();
                                                                    }

                                                                    // Edit mode: Use existing entitlement value if exists
                                                                    // Add mode: Use default calculated value from leave type
                                                                    if ($isEditMode ?? false) {
                                                                        // Edit mode - use existing entitlement value for the selected period
                                                                        $defaultValue = $existingEntitlement
                                                                            ? $existingEntitlement->entitled_days
                                                                            : $leave['calculated_days'];
                                                                    } else {
                                                                        // Add mode - use default calculated value from leave type
                                                                        $defaultValue = $leave['calculated_days'];
                                                                    }
                                                                @endphp
                                                                <tr
                                                                    class="{{ strtolower($cat) === 'lsl' ? 'lsl-table-row' : '' }}">
                                                                    <td>
                                                                        <strong>{{ $leave['name'] }}</strong>
                                                                        @if ($leave['code'] ?? null)
                                                                            <br><small
                                                                                class="text-muted">{{ $leave['code'] }}</small>
                                                                        @endif
                                                                        @if ($rowPeriodDates)
                                                                            <br>
                                                                            <x-leave-entitlement-period :start="$rowPeriodDates['start']"
                                                                                :end="$rowPeriodDates['end']" :category="strtolower($cat) === 'lsl' ? 'lsl' : null"
                                                                                variant="badge" />
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if (in_array(strtolower($cat), ['lsl', 'annual'], true) && ($leave['carried_over'] ?? 0) > 0)
                                                                            <span class="badge badge-secondary">{{ $leave['base_days'] ?? $leave['default_days'] }} days</span>
                                                                            <br><small class="text-success">+ {{ $leave['carried_over'] }} carry over</small>
                                                                        @else
                                                                            <span class="badge badge-secondary">{{ $leave['default_days'] }}
                                                                                days</span>
                                                                        @endif
                                                                        @if ($existingEntitlement && in_array(strtolower($cat), ['lsl', 'annual'], true) && ($leave['default_days'] ?? 0) < $existingEntitlement->entitled_days)
                                                                            @php
                                                                                $storedCarryOver = max(0, $existingEntitlement->entitled_days - (int) ($leave['default_days'] ?? 0));
                                                                            @endphp
                                                                            <br><small class="text-muted">Termasuk carry over: {{ $storedCarryOver }} hari</small>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <div class="input-group input-group-sm"
                                                                            style="max-width: 150px; margin: 0 auto;">
                                                                            <input type="number"
                                                                                name="entitlements[{{ $globalIndex }}][entitled_days]"
                                                                                class="form-control text-center"
                                                                                value="{{ old('entitlements.' . $globalIndex . '.entitled_days', $defaultValue) }}"
                                                                                min="0"
                                                                                max="{{ strtolower($cat) === 'lsl' ? 999 : 365 }}">
                                                                            <div class="input-group-append">
                                                                                <span
                                                                                    class="input-group-text"><small>days</small></span>
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden"
                                                                            name="entitlements[{{ $globalIndex }}][leave_type_id]"
                                                                            value="{{ $leave['id'] }}">
                                                                        @if ($rowPeriodDates)
                                                                            <input type="hidden"
                                                                                name="entitlements[{{ $globalIndex }}][period_start]"
                                                                                value="{{ $rowPeriodDates['start']->format('Y-m-d') }}">
                                                                            <input type="hidden"
                                                                                name="entitlements[{{ $globalIndex }}][period_end]"
                                                                                value="{{ $rowPeriodDates['end']->format('Y-m-d') }}">
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if ($existingEntitlement && $existingEntitlement->taken_days > 0)
                                                                            <span class="badge badge-warning">
                                                                                {{ $existingEntitlement->taken_days }}/{{ $existingEntitlement->entitled_days }}
                                                                            </span>
                                                                        @elseif ($existingEntitlement)
                                                                            <span class="badge badge-secondary">
                                                                                {{ $existingEntitlement->taken_days }}/{{ $existingEntitlement->entitled_days }}
                                                                            </span>
                                                                        @else
                                                                            <span class="badge badge-light">-</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @php $globalIndex++; @endphp
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('leave.entitlements.employee.show', $employee->id) }}"
                                            class="btn btn-secondary">
                                            <i class="fas fa-arrow-left mr-2"></i> Back to Summary
                                        </a>
                                        <button type="submit" class="btn btn-{{ $isLslScope ? 'warning' : 'success' }}">
                                            <i class="fas fa-{{ $isEditMode ?? false ? 'save' : 'plus' }} mr-2"></i>
                                            {{ ($isEditMode ?? false ? 'Simpan Perubahan' : 'Simpan Hak Cuti') . ' ' . ($scopeTitle ?? 'Tahunan') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </section>
@endsection

@push('styles')
    @include('leave-entitlements.partials.period-styles')
    <style>
        /* Info Banner - Same style as travel-header, Full Width */
        .info-banner {
            position: relative;
            width: 100%;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .info-banner--lsl {
            background: linear-gradient(135deg, #856404 0%, #ffc107 100%);
            color: #212529;
        }

        .info-banner--lsl .employee-details,
        .info-banner--lsl .period-info,
        .info-banner--lsl .leave-period-banner__type {
            color: rgba(33, 37, 41, 0.85);
        }

        .card-warning.card-outline {
            border-top: 3px solid #ffc107;
        }

        .info-banner-content {
            position: relative;
            z-index: 2;
            height: 100%;
            max-width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .employee-info {
            flex: 1;
        }

        .employee-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .employee-details {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .period-info {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .period-year {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 20px;
            font-weight: 600;
            opacity: 0.9;
        }

        /* Sidebar Info - AdminLTE compatible */
        .info-sidebar .info-section {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .info-sidebar .info-section:last-child {
            border-bottom: none;
        }

        .info-sidebar .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .info-sidebar .info-label i {
            width: 20px;
            text-align: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .info-banner {
                height: auto;
                min-height: 120px;
            }

            .period-year {
                position: relative;
                top: 0;
                right: 0;
                margin-top: 10px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Tab functionality
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                // Focus first input in active tab
                var targetTab = $(e.target).attr('href');
                $(targetTab).find('input[type="number"]').first().focus();
            });

            // Input validation and formatting
            $('input[type="number"]').on('input', function() {
                var value = parseInt($(this).val()) || 0;
                var maxVal = parseInt($(this).attr('max')) || 365;
                if (value < 0) {
                    $(this).val(0);
                } else if (value > maxVal) {
                    $(this).val(maxVal);
                }
            });

            // Auto-select input value on focus
            $('input[type="number"]').on('focus', function() {
                $(this).select();
            });

            // Enter key to move to next input
            $('input[type="number"]').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var inputs = $(this).closest('tbody').find('input[type="number"]');
                    var currentIndex = inputs.index(this);
                    if (currentIndex < inputs.length - 1) {
                        inputs.eq(currentIndex + 1).focus().select();
                    }
                }
            });
        });
    </script>
@endpush
