@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Rincian Perhitungan Cuti</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('leave.entitlements.index') }}">Leave
                                    Entitlements</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('leave.entitlements.employee.show', $employee->id) }}">{{ $employee->fullname }}</a>
                            </li>
                            <li class="breadcrumb-item active">Calculation Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Employee Info Card - Compact -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user mr-1"></i>
                                    {{ $employee->fullname }}
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-primary">{{ $leaveType->name }}</span>
                                </div>
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="text-muted small">NIK</div>
                                            <div class="font-weight-bold">
                                                {{ $employee->administrations->first()->nik ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="text-muted small">Project</div>
                                            <div class="font-weight-bold">
                                                {{ $employee->administrations->first()->project->project_code ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="text-muted small">Level</div>
                                            <div class="font-weight-bold">
                                                {{ $employee->administrations->first()->level->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="text-muted small">DOH</div>
                                            <div class="font-weight-bold">
                                                {{ $employee->administrations->first()->doh ? $employee->administrations->first()->doh->format('d M Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="text-muted small">Periode Entitlement</div>
                                            <div class="font-weight-bold">
                                                {{ $calculationDetails['entitlement_period']['start'] }} s/d
                                                {{ $calculationDetails['entitlement_period']['end'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Chart - Full Width -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar mr-1"></i>
                                    Progress Penggunaan Cuti
                                </h3>
                                @if (isset($calculationDetails['total_cancelled_days']) && $calculationDetails['total_cancelled_days'] > 0)
                                    <div class="card-tools">
                                        <span class="badge badge-warning">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            {{ $calculationDetails['total_cancelled_days'] }} hari dicancel
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="progress progress-lg">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $calculationDetails['calculation_summary']['utilization_percentage'] }}%"
                                        role="progressbar">
                                        {{ $calculationDetails['calculation_summary']['utilization_percentage'] }}%
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-3 text-center">
                                        <div class="text-sm text-muted">Total Hak</div>
                                        <div class="h4 text-info">
                                            {{ $calculationDetails['calculation_summary']['total_entitlement'] }}</div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="text-sm text-muted">Sudah Diambil</div>
                                        <div class="h4 text-warning">
                                            {{ $calculationDetails['calculation_summary']['total_taken'] }}</div>
                                        @if (isset($calculationDetails['total_cancelled_days']) && $calculationDetails['total_cancelled_days'] > 0)
                                            <div class="text-xs text-muted">
                                                ({{ $calculationDetails['total_cancelled_days'] }} dicancel)
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="text-sm text-muted">Efektif Digunakan</div>
                                        <div class="h4 text-success">
                                            {{ $calculationDetails['calculation_summary']['total_effective'] ?? $calculationDetails['calculation_summary']['total_taken'] }}
                                        </div>
                                        @if (isset($calculationDetails['total_cancelled_days']) && $calculationDetails['total_cancelled_days'] > 0)
                                            <div class="text-xs text-success">
                                                ({{ $calculationDetails['calculation_summary']['total_taken'] - ($calculationDetails['calculation_summary']['total_effective'] ?? $calculationDetails['calculation_summary']['total_taken']) }}
                                                dikembalikan)
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <div class="text-sm text-muted">Sisa Hari</div>
                                        <div class="h4 text-success">
                                            {{ $calculationDetails['calculation_summary']['remaining'] }}</div>
                                        <div class="text-xs text-muted">
                                            ({{ $calculationDetails['calculation_summary']['utilization_percentage'] }}%
                                            digunakan)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave History -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-secondary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-1"></i>
                                    Riwayat Pengambilan Cuti
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-secondary">{{ count($calculationDetails['leave_requests']) }}
                                        Request</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                @if (count($calculationDetails['leave_requests']) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="20%">Tanggal</th>
                                                    <th width="8%">Durasi</th>
                                                    <th width="8%">Dicancel</th>
                                                    <th width="8%">Efektif</th>
                                                    <th width="25%">Alasan</th>
                                                    <th width="15%">Disetujui</th>
                                                    <th width="11%">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($calculationDetails['leave_requests'] as $index => $request)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="text-sm">
                                                                <strong>{{ $request['start_date'] }}</strong><br>
                                                                <span class="text-muted">s/d
                                                                    {{ $request['end_date'] }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">{{ $request['total_days'] }}
                                                                hari</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if (isset($request['cancelled_days']) && $request['cancelled_days'] > 0)
                                                                <span
                                                                    class="badge badge-warning">{{ $request['cancelled_days'] }}
                                                                    hari</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if (isset($request['effective_days']))
                                                                <span
                                                                    class="badge badge-success">{{ $request['effective_days'] }}
                                                                    hari</span>
                                                            @else
                                                                <span
                                                                    class="badge badge-primary">{{ $request['total_days'] }}
                                                                    hari</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="text-sm">{{ $request['reason'] ?? '-' }}</span>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="text-sm text-muted">{{ $request['approved_at'] ?? '-' }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if (isset($request['status']) && $request['status'] === 'cancelled')
                                                                <span class="badge badge-danger">Cancelled</span>
                                                            @elseif(isset($request['cancelled_days']) && $request['cancelled_days'] > 0)
                                                                <span class="badge badge-warning">Partially
                                                                    Cancelled</span>
                                                            @else
                                                                <span class="badge badge-success">Approved</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light">
                                                <tr>
                                                    <th colspan="2" class="text-right">Total:</th>
                                                    <th class="text-center">{{ $calculationDetails['taken_days'] }} hari
                                                    </th>
                                                    <th class="text-center">
                                                        @if (isset($calculationDetails['total_cancelled_days']) && $calculationDetails['total_cancelled_days'] > 0)
                                                            <span
                                                                class="badge badge-warning">{{ $calculationDetails['total_cancelled_days'] }}
                                                                hari</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </th>
                                                    <th class="text-center">
                                                        @if (isset($calculationDetails['total_effective_days']))
                                                            <span
                                                                class="badge badge-success">{{ $calculationDetails['total_effective_days'] }}
                                                                hari</span>
                                                        @else
                                                            <span
                                                                class="badge badge-primary">{{ $calculationDetails['taken_days'] }}
                                                                hari</span>
                                                        @endif
                                                    </th>
                                                    <th colspan="3"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum Ada Riwayat Pengambilan Cuti</h5>
                                        <p class="text-muted">Karyawan ini belum pernah mengambil cuti
                                            {{ $leaveType->name }} pada periode ini.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="{{ route('leave.entitlements.employee.show', $employee) }}"
                                            class="btn btn-secondary">
                                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Entitlements
                                        </a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="button" class="btn btn-info" onclick="window.print()">
                                            <i class="fas fa-print mr-1"></i> Cetak Laporan
                                        </button>
                                        <a href="{{ route('leave.entitlements.employee.edit', $employee) }}"
                                            class="btn btn-warning">
                                            <i class="fas fa-edit mr-1"></i> Edit Entitlements
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('styles')
        <style>
            /* AdminLTE Customizations */
            .small-box .inner h3 {
                font-size: 2.2rem;
                font-weight: bold;
            }

            .small-box .inner p {
                font-size: 1rem;
            }

            .info-box .info-box-number {
                font-size: 1.5rem;
                font-weight: bold;
            }

            .table-sm th,
            .table-sm td {
                padding: 0.5rem;
            }

            .progress-lg {
                height: 1.5rem;
            }

            .progress-lg .progress-bar {
                font-size: 0.9rem;
                font-weight: bold;
            }

            .callout {
                margin-bottom: 0;
            }

            .card-tools .badge {
                font-size: 0.8rem;
            }

            /* Print Styles */
            @media print {

                .btn,
                .card-tools,
                .breadcrumb {
                    display: none !important;
                }

                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                }

                .small-box {
                    border: 1px solid #ddd !important;
                }
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .small-box .inner h3 {
                    font-size: 1.8rem;
                }

                .info-box .info-box-number {
                    font-size: 1.2rem;
                }

                .table-responsive {
                    font-size: 0.9rem;
                }
            }
        </style>
    @endpush
@endsection

