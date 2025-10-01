@extends('layouts.main')

@section('title', 'Edit Employee Leave Entitlements - ' . $employee->fullname)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Employee Leave Entitlements</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.entitlements.index') }}">Leave
                                Entitlements</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('leave.entitlements.employee.show', $employee->id) }}">{{ $employee->fullname }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Leave Business Rules Card -->
            @if ($businessRules)
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Leave Entitlement Business Rules</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Employee Classification</h5>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th width="35%">NIK:</th>
                                        <td>{{ $employee->administrations->first()->nik ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td>{{ $employee->fullname }}</td>
                                    </tr>
                                    <tr>
                                        <th>Project:</th>
                                        <td>
                                            {{ $employee->administrations->first()->project->project_code ?? 'N/A' }} -
                                            {{ $employee->administrations->first()->project->project_name ?? 'N/A' }}
                                            @if ($employee->administrations->first()->project)
                                                <span
                                                    class="badge badge-{{ $employee->administrations->first()->project->leave_type === 'roster' ? 'warning' : 'info' }}">
                                                    {{ ucfirst($employee->administrations->first()->project->leave_type) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Level:</th>
                                        <td>{{ $employee->administrations->first()->level->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>DOH:</th>
                                        <td>{{ $employee->administrations->first()->doh ? $employee->administrations->first()->doh->format('d F Y') : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge badge-success">Active</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Project Group:</th>
                                        <td>
                                            <span
                                                class="badge badge-{{ str_contains($businessRules['project_group'], 'Roster') ? 'warning' : 'info' }}">
                                                {{ $businessRules['project_group'] }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Staff Type:</th>
                                        <td>
                                            <span class="badge badge-secondary">{{ $businessRules['staff_type'] }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Years of Service:</th>
                                        <td>
                                            <strong>{{ $businessRules['years_of_service'] }} years</strong>
                                            <small class="text-muted">({{ $businessRules['months_of_service'] }}
                                                months)</small>
                                        </td>
                                    </tr>
                                    @if (!empty($businessRules['special_notes']))
                                        <tr>
                                            <th colspan="2" class="pt-3"><strong>Special Rules:</strong></th>
                                        </tr>
                                        @foreach ($businessRules['special_notes'] as $note)
                                            <tr>
                                                <td colspan="2">
                                                    <i class="fas fa-arrow-right text-info"></i>
                                                    <small>{{ $note }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </table>
                            </div>
                            <!-- Form wrapper -->
                            <div class="col-md-6">
                                <form method="POST"
                                    action="{{ route('leave.entitlements.employee.update', $employee->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <h5 class="mb-3">Eligible Leave Types</h5>

                                    <!-- Leave Period Information -->
                                    <div class="form-group mb-3">
                                        <label>Leave Period {{ $currentYear }}</label>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar"></i>
                                            @if ($periodDates)
                                                Period: {{ $periodDates['start']->format('d M Y') }} -
                                                {{ $periodDates['end']->format('d M Y') }}
                                            @endif
                                        </small>
                                    </div>

                                    @php
                                        $groupedLeavesForDisplay = collect($businessRules['eligible_leaves'])->groupBy(
                                            'category',
                                        );
                                        $globalIndex = 0; // Global counter for unique form indices
                                    @endphp

                                    <!-- Collapse All Button -->
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="collapseAllBtn">
                                            <i class="fas fa-expand-arrows-alt"></i> Expand All Leave Types
                                        </button>
                                    </div>

                                    <div class="accordion" id="eligibleLeavesAccordion">
                                        @foreach ($groupedLeavesForDisplay as $cat => $catLeaves)
                                            @php
                                                $catId = str_replace(' ', '', strtolower($cat));
                                            @endphp

                                            <div class="card card-secondary card-outline mb-2">
                                                <div class="card-header py-2" id="heading{{ $catId }}">
                                                    <h6 class="mb-0">
                                                        <button class="btn btn-link text-left w-100" type="button"
                                                            data-toggle="collapse"
                                                            data-target="#collapse{{ $catId }}"
                                                            aria-expanded="false"
                                                            aria-controls="collapse{{ $catId }}">
                                                            <i class="fas fa-list mr-2"></i>
                                                            {{ ucfirst($cat) }} Leave
                                                            <span
                                                                class="badge badge-secondary ml-2">{{ $catLeaves->count() }}</span>
                                                            <i class="fas fa-chevron-down float-right"></i>
                                                        </button>
                                                    </h6>
                                                </div>
                                                <div id="collapse{{ $catId }}" class="collapse"
                                                    aria-labelledby="heading{{ $catId }}">
                                                    <div class="card-body p-0">
                                                        <div class="p-3">
                                                            <div class="row">
                                                                @foreach ($catLeaves as $leave)
                                                                    @php
                                                                        // Find existing entitlement with proper period matching
                                                                        $existingEntitlement = $employee->leaveEntitlements
                                                                            ->where('leave_type_id', $leave['id'])
                                                                            ->where(
                                                                                'period_start',
                                                                                $periodDates['start']->format('Y-m-d'),
                                                                            )
                                                                            ->where(
                                                                                'period_end',
                                                                                $periodDates['end']->format('Y-m-d'),
                                                                            )
                                                                            ->first();

                                                                        // Fallback: If no exact period match, find any entitlement for this leave type
                                                                        if (!$existingEntitlement) {
                                                                            $existingEntitlement = $employee->leaveEntitlements
                                                                                ->where('leave_type_id', $leave['id'])
                                                                                ->first();
                                                                        }

                                                                        $defaultValue = $existingEntitlement
                                                                            ? $existingEntitlement->entitled_days
                                                                            : $leave['calculated_days'];
                                                                    @endphp

                                                                    <div class="col-md-12 mb-2">
                                                                        <div class="card border-secondary shadow-sm">
                                                                            <div class="card-body p-2">
                                                                                <div class="row align-items-center">
                                                                                    <div class="col-md-5 text-left">
                                                                                        <strong
                                                                                            class="small">{{ $leave['name'] }}</strong>
                                                                                    </div>
                                                                                    <div class="col-md-4 text-right">
                                                                                        <div
                                                                                            class="input-group input-group-sm">
                                                                                            <input type="number"
                                                                                                name="entitlements[{{ $globalIndex }}][entitled_days]"
                                                                                                class="form-control form-control-sm text-right"
                                                                                                value="{{ old('entitlements.' . $globalIndex . '.entitled_days', $defaultValue) }}"
                                                                                                min="0"
                                                                                                max="365">
                                                                                            <input type="hidden"
                                                                                                name="entitlements[{{ $globalIndex }}][leave_type_id]"
                                                                                                value="{{ $leave['id'] }}">
                                                                                            <div class="input-group-append">
                                                                                                <span
                                                                                                    class="input-group-text bg-secondary text-white">
                                                                                                    <small>days</small>
                                                                                                </span>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-3 text-right">
                                                                                        <small class="text-muted d-block">
                                                                                            <i
                                                                                                class="fas fa-info-circle"></i>
                                                                                            Default:
                                                                                            {{ $leave['default_days'] }}
                                                                                        </small>
                                                                                        @if ($existingEntitlement)
                                                                                            <small
                                                                                                class="badge badge-secondary badge-sm">
                                                                                                Used:
                                                                                                {{ $existingEntitlement->taken_days }}/{{ $existingEntitlement->entitled_days }}
                                                                                            </small>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @php $globalIndex++; @endphp
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="form-group mt-3">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Update Entitlements
                                        </button>
                                        <a href="{{ route('leave.entitlements.employee.show', $employee->id) }}"
                                            class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </section>
@endsection

@push('styles')
    <style>
        /* AdminLTE Standard Accordion Styles */
        .accordion .card-header .btn-link {
            text-decoration: none;
            color: #495057;
            font-weight: 500;
        }

        .accordion .card-header .btn-link:hover {
            text-decoration: none;
            color: #007bff;
        }

        .accordion .card-header .btn-link:focus {
            text-decoration: none;
            box-shadow: none;
        }

        .accordion .card-header .fa-chevron-down,
        .accordion .card-header .fa-chevron-up {
            transition: transform 0.2s ease-in-out;
        }

        /* Collapse All Button */
        #collapseAllBtn {
            transition: all 0.2s ease-in-out;
        }

        #collapseAllBtn:hover {
            transform: translateY(-1px);
        }

        /* Card Shadow Effects */
        .card.shadow-sm {
            transition: box-shadow 0.2s ease-in-out;
        }

        .card.shadow-sm:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .accordion .card-header {
                padding: 0.75rem;
            }

            .accordion .card-header h6 {
                font-size: 0.9rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            console.log('Leave entitlement form loaded for year {{ $currentYear }}');

            // Collapse All Button functionality
            let allExpanded = false;
            $('#collapseAllBtn').click(function() {
                if (allExpanded) {
                    // Collapse all
                    $('.collapse').collapse('hide');
                    $(this).html('<i class="fas fa-expand-arrows-alt"></i> Expand All Leave Types');
                    allExpanded = false;
                } else {
                    // Expand all
                    $('.collapse').collapse('show');
                    $(this).html('<i class="fas fa-compress-arrows-alt"></i> Collapse All Leave Types');
                    allExpanded = true;
                }
            });

            // Accordion animation and chevron rotation
            $('.collapse').on('show.bs.collapse', function() {
                var target = $(this).attr('aria-labelledby');
                $('#' + target + ' .fa-chevron-down').removeClass('fa-chevron-down').addClass(
                    'fa-chevron-up');
            });

            $('.collapse').on('hide.bs.collapse', function() {
                var target = $(this).attr('aria-labelledby');
                $('#' + target + ' .fa-chevron-up').removeClass('fa-chevron-up').addClass(
                    'fa-chevron-down');
            });

            // Update button text when all are expanded/collapsed
            $('.collapse').on('shown.bs.collapse hidden.bs.collapse', function() {
                var visibleCount = $('.collapse.show').length;
                var totalCount = $('.collapse').length;

                if (visibleCount === totalCount) {
                    $('#collapseAllBtn').html(
                        '<i class="fas fa-compress-arrows-alt"></i> Collapse All Leave Types');
                    allExpanded = true;
                } else if (visibleCount === 0) {
                    $('#collapseAllBtn').html(
                        '<i class="fas fa-expand-arrows-alt"></i> Expand All Leave Types');
                    allExpanded = false;
                }
            });
        });
    </script>
@endpush
