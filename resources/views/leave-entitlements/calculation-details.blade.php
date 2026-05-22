@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Rincian Perhitungan Cuti</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            @if (isset($fromMyEntitlementsCalculation) && $fromMyEntitlementsCalculation)
                                <li class="breadcrumb-item"><a href="{{ route('leave.my-entitlements') }}">Hak Cuti Saya</a></li>
                            @elseif (auth()->user()->can('leave-entitlements.show'))
                                <li class="breadcrumb-item"><a href="{{ route('leave.entitlements.index') }}">Hak Cuti</a></li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('leave.entitlements.employee.show', $employee->id) }}">{{ $employee->fullname }}</a>
                                </li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('leave.my-entitlements') }}">Hak Cuti Saya</a></li>
                            @endif
                            <li class="breadcrumb-item active">Rincian Perhitungan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @php
                    $activeAdministrationForCalc = $employee->administrations->where('is_active', 1)->first();
                    $allAdministrationsForCalc = $employee->administrations->whereNotNull('doh')->sortBy('doh')->values();

                    $calcServiceStartDoh = null;
                    $calcServiceStartNik = null;

                    if ($allAdministrationsForCalc->count() > 0) {
                        $calcServiceStartDoh = $allAdministrationsForCalc->first()->doh;
                        $calcServiceStartNik = $allAdministrationsForCalc->first()->nik;

                        foreach ($allAdministrationsForCalc as $adm) {
                            if ($adm->termination_date && $adm->termination_reason) {
                                $terminationReason = strtolower(trim($adm->termination_reason));

                                if ($terminationReason !== 'end of contract') {
                                    $nextAdmin = $allAdministrationsForCalc->firstWhere(function ($next) use ($adm) {
                                        return $next->doh > $adm->termination_date;
                                    });

                                    if ($nextAdmin) {
                                        $calcServiceStartDoh = $nextAdmin->doh;
                                        $calcServiceStartNik = $nextAdmin->nik;
                                    }
                                }
                            }
                        }
                    }

                    $summary = $calculationDetails['calculation_summary'];
                    $isLsl = ($leaveType->category ?? null) === 'lsl';
                    $periodStartVal = $periodStart ?? ($calculationDetails['entitlement_period']['start'] ?? null);
                    $periodEndVal = $periodEnd ?? ($calculationDetails['entitlement_period']['end'] ?? null);
                    $utilPct = min(100, max(0, $summary['utilization_percentage']));
                    $hasCancelled = ($calculationDetails['total_cancelled_days'] ?? 0) > 0;
                    $requestCount = count($calculationDetails['leave_requests']);
                    $carriedOver = (int) ($calculationDetails['carried_over'] ?? 0);
                    $baseEntitlement = (int) ($calculationDetails['base_entitlement'] ?? $summary['total_entitlement']);
                @endphp

                {{-- Hero Banner --}}
                <div class="calc-banner {{ $isLsl ? 'calc-banner--lsl' : 'calc-banner--annual' }}">
                    <div class="calc-banner__body">
                        <div class="calc-banner__main">
                            <div class="calc-banner__name">{{ $employee->fullname }}</div>
                            <div class="calc-banner__meta">
                                {{ $activeAdministrationForCalc?->project?->project_code ?? 'N/A' }}
                                · {{ $activeAdministrationForCalc?->nik ?? 'N/A' }}
                            </div>
                            @if ($periodStartVal && $periodEndVal)
                                <x-leave-entitlement-period
                                    :start="$periodStartVal"
                                    :end="$periodEndVal"
                                    :category="$leaveType->category ?? null"
                                    variant="banner"
                                />
                            @endif
                        </div>
                        <div class="calc-banner__aside">
                            <span class="calc-banner__leave-type">
                                <i class="fas fa-{{ $isLsl ? 'hourglass-half' : 'calendar-check' }} mr-1"></i>
                                {{ $leaveType->name }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Employee Meta --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card card-outline card-light calc-meta-card mb-0">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-6 col-md-3 calc-meta-item">
                                        <div class="calc-meta-item__label">Level</div>
                                        <div class="calc-meta-item__value">{{ $activeAdministrationForCalc?->level?->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-6 col-md-3 calc-meta-item">
                                        <div class="calc-meta-item__label">DOH</div>
                                        <div class="calc-meta-item__value">
                                            @if ($calcServiceStartDoh)
                                                {{ \Carbon\Carbon::parse($calcServiceStartDoh)->format('d M Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                        @if ($calcServiceStartNik && $activeAdministrationForCalc && $calcServiceStartNik != $activeAdministrationForCalc->nik)
                                            <div class="calc-meta-item__hint">
                                                <i class="fas fa-info-circle"></i> dari NIK {{ $calcServiceStartNik }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-12 col-md-6 mt-3 mt-md-0">
                                        @if ($periodStartVal && $periodEndVal)
                                            <x-leave-entitlement-period
                                                :start="$periodStartVal"
                                                :end="$periodEndVal"
                                                :category="$leaveType->category ?? null"
                                                variant="panel"
                                                :show-help="true"
                                            />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Summary Stats --}}
                <div class="row">
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="calc-stat calc-stat--info">
                            <div class="calc-stat__icon"><i class="fas fa-gift"></i></div>
                            <div class="calc-stat__content">
                                <div class="calc-stat__value">{{ $summary['total_entitlement'] }}</div>
                                <div class="calc-stat__label">Total Hak</div>
                                @if (($isLsl || ($leaveType->category ?? '') === 'annual') && $carriedOver > 0)
                                    <div class="calc-stat__sub text-info">{{ $baseEntitlement }} dasar + {{ $carriedOver }} carry over</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="calc-stat calc-stat--warning">
                            <div class="calc-stat__icon"><i class="fas fa-plane-departure"></i></div>
                            <div class="calc-stat__content">
                                <div class="calc-stat__value">{{ $summary['total_taken'] }}</div>
                                <div class="calc-stat__label">Sudah Diambil</div>
                                @if ($hasCancelled)
                                    <div class="calc-stat__sub">{{ $calculationDetails['total_cancelled_days'] }} dicancel</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="calc-stat calc-stat--primary">
                            <div class="calc-stat__icon"><i class="fas fa-check-double"></i></div>
                            <div class="calc-stat__content">
                                <div class="calc-stat__value">{{ $summary['total_effective'] ?? $summary['total_taken'] }}</div>
                                <div class="calc-stat__label">Efektif Digunakan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 mb-3">
                        <div class="calc-stat calc-stat--success">
                            <div class="calc-stat__icon"><i class="fas fa-wallet"></i></div>
                            <div class="calc-stat__content">
                                <div class="calc-stat__value">{{ $summary['remaining'] }}</div>
                                <div class="calc-stat__label">Sisa Hari</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Utilization Progress --}}
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="card card-outline card-success calc-progress-card mb-0">
                            <div class="card-header py-2">
                                <h3 class="card-title text-sm">
                                    <i class="fas fa-chart-pie mr-1"></i> Penggunaan Cuti
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-success">{{ $utilPct }}% digunakan</span>
                                    @if ($hasCancelled)
                                        <span class="badge badge-warning ml-1">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            {{ $calculationDetails['total_cancelled_days'] }} hari dicancel
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body pt-3 pb-3">
                                <div class="calc-progress-track">
                                    <div class="calc-progress-used" style="width: {{ $utilPct }}%"></div>
                                </div>
                                <div class="calc-progress-legend">
                                    <span><i class="fas fa-circle text-success"></i> Digunakan ({{ $summary['total_effective'] ?? $summary['total_taken'] }} hari)</span>
                                    <span><i class="fas fa-circle text-light border rounded-circle" style="font-size:0.55rem;vertical-align:middle;"></i> Sisa ({{ $summary['remaining'] }} hari)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Leave History --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-secondary calc-history-card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-1"></i>
                                    Riwayat Pengambilan Cuti
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-secondary">{{ $requestCount }} permintaan</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                @if ($requestCount > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover calc-history-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="4%" class="text-center">#</th>
                                                    <th width="18%">Tanggal</th>
                                                    <th width="8%" class="text-center">Durasi</th>
                                                    <th width="8%" class="text-center">Dicancel</th>
                                                    <th width="8%" class="text-center">Efektif</th>
                                                    <th width="26%">Alasan</th>
                                                    <th width="16%">Disetujui</th>
                                                    <th width="12%" class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($calculationDetails['leave_requests'] as $index => $request)
                                                    @php
                                                        $isCancelled = ($request['status'] ?? '') === 'cancelled';
                                                        $isPartial = ! $isCancelled && ($request['cancelled_days'] ?? 0) > 0;
                                                        $rowClass = $isCancelled ? 'calc-row--cancelled' : ($isPartial ? 'calc-row--partial' : '');
                                                    @endphp
                                                    <tr class="{{ $rowClass }}">
                                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="calc-date-range">
                                                                <strong>{{ $request['start_date'] }}</strong>
                                                                <span class="text-muted">s/d {{ $request['end_date'] }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-light border">{{ $request['total_days'] }} hari</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if (($request['cancelled_days'] ?? 0) > 0)
                                                                <span class="badge badge-warning">{{ $request['cancelled_days'] }} hari</span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-{{ $isCancelled ? 'secondary' : 'success' }}">
                                                                {{ $request['effective_days'] ?? $request['total_days'] }} hari
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="calc-reason">{{ $request['reason'] ?? '—' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-muted small">{{ $request['approved_at'] ?? '—' }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($isCancelled)
                                                                <span class="badge badge-danger"><i class="fas fa-ban mr-1"></i>Dibatalkan</span>
                                                            @elseif ($isPartial)
                                                                <span class="badge badge-warning"><i class="fas fa-adjust mr-1"></i>Sebagian Dibatalkan</span>
                                                            @else
                                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Disetujui</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="calc-history-total">
                                                    <th colspan="2" class="text-right">Total</th>
                                                    <th class="text-center">{{ $calculationDetails['taken_days'] }} hari</th>
                                                    <th class="text-center">
                                                        @if ($hasCancelled)
                                                            <span class="badge badge-warning">{{ $calculationDetails['total_cancelled_days'] }} hari</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </th>
                                                    <th class="text-center">
                                                        <span class="badge badge-success">
                                                            {{ $calculationDetails['total_effective_days'] ?? $calculationDetails['taken_days'] }} hari
                                                        </span>
                                                    </th>
                                                    <th colspan="3"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="calc-empty">
                                        <div class="calc-empty__icon"><i class="fas fa-calendar-times"></i></div>
                                        <h5>Belum Ada Riwayat Pengambilan Cuti</h5>
                                        <p>Karyawan belum pernah mengambil cuti <strong>{{ $leaveType->name }}</strong> pada periode ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="calc-actions">
                    @if (isset($fromMyEntitlementsCalculation) && $fromMyEntitlementsCalculation)
                        <a href="{{ route('leave.my-entitlements') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    @elseif (auth()->user()->can('leave-entitlements.show'))
                        <a href="{{ route('leave.entitlements.employee.show', $employee) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Entitlements
                        </a>
                    @else
                        <a href="{{ route('leave.my-entitlements') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    @endif

                    @if (auth()->user()->can('leave-entitlements.edit'))
                        <a href="{{ route('leave.entitlements.employee.show', $employee) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i> Kelola Entitlements
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @push('styles')
        @include('leave-entitlements.partials.period-styles')
        <style>
            /* Banner */
            .calc-banner {
                color: #fff;
                padding: 1.25rem 1.75rem;
                margin-bottom: 1.25rem;
                border-radius: 0.5rem;
                background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
            }

            .calc-banner--lsl {
                background: linear-gradient(135deg, #856404 0%, #ffc107 100%);
                color: #212529;
            }

            .calc-banner--lsl .calc-banner__meta,
            .calc-banner--lsl .leave-period-banner__type {
                color: rgba(33, 37, 41, 0.8);
            }

            .calc-banner__body {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
            }

            .calc-banner__name {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 0.25rem;
            }

            .calc-banner__meta {
                font-size: 0.8125rem;
                opacity: 0.9;
                letter-spacing: 0.03em;
                margin-bottom: 0.35rem;
            }

            .calc-banner__leave-type {
                display: inline-block;
                font-size: 1rem;
                font-weight: 600;
                padding: 0.45rem 0.85rem;
                border-radius: 0.35rem;
                background: rgba(255, 255, 255, 0.18);
                white-space: nowrap;
            }

            .calc-banner--lsl .calc-banner__leave-type {
                background: rgba(0, 0, 0, 0.08);
            }

            /* Meta card */
            .calc-meta-card {
                border-top: 3px solid #dee2e6;
            }

            .calc-meta-item__label {
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #6c757d;
                font-weight: 600;
                margin-bottom: 0.15rem;
            }

            .calc-meta-item__value {
                font-weight: 600;
                font-size: 0.95rem;
            }

            .calc-meta-item__hint {
                font-size: 0.75rem;
                color: #17a2b8;
                margin-top: 0.15rem;
            }

            .calc-meta-card .leave-period-panel {
                margin-bottom: 0 !important;
            }

            /* Stat cards */
            .calc-stat {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                padding: 1rem 1.15rem;
                border-radius: 0.5rem;
                background: #fff;
                border: 1px solid #e9ecef;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
                height: 100%;
            }

            .calc-stat__icon {
                width: 2.75rem;
                height: 2.75rem;
                border-radius: 0.5rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.15rem;
                flex-shrink: 0;
            }

            .calc-stat--info .calc-stat__icon { background: rgba(23, 162, 184, 0.12); color: #17a2b8; }
            .calc-stat--warning .calc-stat__icon { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
            .calc-stat--primary .calc-stat__icon { background: rgba(0, 123, 255, 0.12); color: #007bff; }
            .calc-stat--success .calc-stat__icon { background: rgba(40, 167, 69, 0.12); color: #28a745; }

            .calc-stat__value {
                font-size: 1.65rem;
                font-weight: 700;
                line-height: 1.1;
            }

            .calc-stat__label {
                font-size: 0.8rem;
                color: #6c757d;
                margin-top: 0.1rem;
            }

            .calc-stat__sub {
                font-size: 0.7rem;
                color: #d39e00;
                margin-top: 0.1rem;
            }

            /* Progress */
            .calc-progress-card .card-header {
                background: #f8f9fa;
            }

            .calc-progress-track {
                height: 1.25rem;
                background: #e9ecef;
                border-radius: 999px;
                overflow: hidden;
            }

            .calc-progress-used {
                height: 100%;
                background: linear-gradient(90deg, #28a745, #20c997);
                border-radius: 999px;
                transition: width 0.4s ease;
                min-width: 0;
            }

            .calc-progress-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                margin-top: 0.65rem;
                font-size: 0.8125rem;
                color: #6c757d;
            }

            .calc-progress-legend i {
                font-size: 0.55rem;
                margin-right: 0.25rem;
                vertical-align: middle;
            }

            /* History table */
            .calc-history-table thead th {
                background: #f4f6f9;
                border-bottom: 2px solid #dee2e6;
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: #495057;
                white-space: nowrap;
            }

            .calc-history-table tbody td {
                vertical-align: middle;
                font-size: 0.875rem;
            }

            .calc-date-range {
                line-height: 1.35;
            }

            .calc-date-range span {
                display: block;
                font-size: 0.8rem;
            }

            .calc-reason {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                font-size: 0.875rem;
            }

            .calc-row--cancelled {
                background-color: #fff5f5;
            }

            .calc-row--partial {
                background-color: #fffdf5;
            }

            .calc-history-total {
                background: #f8f9fa;
                border-top: 2px solid #dee2e6;
            }

            .calc-history-total th {
                font-size: 0.875rem;
            }

            /* Empty state */
            .calc-empty {
                text-align: center;
                padding: 3rem 1.5rem;
                color: #6c757d;
            }

            .calc-empty__icon {
                font-size: 3rem;
                opacity: 0.35;
                margin-bottom: 1rem;
            }

            .calc-empty h5 {
                color: #495057;
                margin-bottom: 0.35rem;
            }

            .calc-empty p {
                margin-bottom: 0;
                font-size: 0.9rem;
            }

            /* Actions */
            .calc-actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 0.75rem;
                margin-top: 1.25rem;
                padding-bottom: 0.5rem;
            }

            /* Print */
            @media print {
                .calc-actions,
                .breadcrumb,
                .card-tools {
                    display: none !important;
                }

                .calc-banner {
                    box-shadow: none;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }

            @media (max-width: 768px) {
                .calc-banner__body {
                    flex-direction: column;
                }

                .calc-banner__leave-type {
                    align-self: flex-start;
                }

                .calc-stat__value {
                    font-size: 1.35rem;
                }

                .calc-history-table {
                    font-size: 0.8125rem;
                }
            }
        </style>
    @endpush
@endsection
