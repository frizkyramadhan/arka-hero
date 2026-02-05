@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Pie Chart: LG by Vendor / Business Partner -->
            <div class="row mb-4">
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Total LG by Business Partner
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-responsive" style="position: relative; height: 320px;">
                                <canvas id="lgByVendorChart"></canvas>
                            </div>
                            @if (empty($issuanceByBusinessPartner))
                                <p class="text-muted text-center mt-2 mb-0">Data not available</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building mr-1"></i>
                                Flight Request, Issuance, and Vendor Statistic
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div id="accordionFlightStats">
                                {{-- Accordion: Flight Request --}}
                                <div class="card border-0 rounded-0 border-bottom">
                                    <div class="card-header p-0 bg-light" id="headingFr">
                                        <button
                                            class="btn btn-link btn-block text-left d-flex align-items-center px-3 py-2 text-dark text-decoration-none"
                                            type="button" data-toggle="collapse" data-target="#collapseFr"
                                            aria-expanded="true" aria-controls="collapseFr">
                                            <i class="fas fa-file-alt mr-2 text-primary"></i>
                                            <span class="small font-weight-bold text-uppercase">Flight Request</span>
                                            <i class="fas fa-chevron-down ml-auto accordion-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapseFr" class="collapse show" aria-labelledby="headingFr"
                                        data-parent="#accordionFlightStats">
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Total</span>
                                                    <span
                                                        class="badge badge-primary badge-pill">{{ $totalFlightRequests }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Pending</span>
                                                    <span
                                                        class="badge badge-secondary badge-pill">{{ $frPending }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Approved</span>
                                                    <span class="badge badge-info badge-pill">{{ $frApproved }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Issued</span>
                                                    <span class="badge badge-success badge-pill">{{ $frIssued }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Completed</span>
                                                    <span class="badge badge-teal badge-pill">{{ $frCompleted }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>This Month</span>
                                                    <span
                                                        class="badge badge-dark badge-pill">{{ $thisMonthFlightRequests }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Accordion: Letter of Guarantee --}}
                                <div class="card border-0 rounded-0 border-bottom">
                                    <div class="card-header p-0 bg-light" id="headingLg">
                                        <button
                                            class="btn btn-link btn-block text-left d-flex align-items-center px-3 py-2 text-dark text-decoration-none collapsed"
                                            type="button" data-toggle="collapse" data-target="#collapseLg"
                                            aria-expanded="false" aria-controls="collapseLg">
                                            <i class="fas fa-file-signature mr-2 text-info"></i>
                                            <span class="small font-weight-bold text-uppercase">Letter of Guarantee
                                                (LG)</span>
                                            <i class="fas fa-chevron-down ml-auto accordion-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapseLg" class="collapse" aria-labelledby="headingLg"
                                        data-parent="#accordionFlightStats">
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Total</span>
                                                    <span
                                                        class="badge badge-primary badge-pill">{{ $totalIssuances }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Pending</span>
                                                    <span
                                                        class="badge badge-warning badge-pill">{{ $issuancePending }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>Approved</span>
                                                    <span
                                                        class="badge badge-success badge-pill">{{ $issuanceApproved }}</span>
                                                </li>
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                    <span>This Month</span>
                                                    <span
                                                        class="badge badge-dark badge-pill">{{ $thisMonthIssuances }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Accordion: Total LG per Vendor --}}
                                <div class="card border-0 rounded-0">
                                    <div class="card-header p-0 bg-light" id="headingVendor">
                                        <button
                                            class="btn btn-link btn-block text-left d-flex align-items-center px-3 py-2 text-dark text-decoration-none collapsed"
                                            type="button" data-toggle="collapse" data-target="#collapseVendor"
                                            aria-expanded="false" aria-controls="collapseVendor">
                                            <i class="fas fa-building mr-2 text-secondary"></i>
                                            <span class="small font-weight-bold text-uppercase">Total LG per Vendor</span>
                                            <i class="fas fa-chevron-down ml-auto accordion-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapseVendor" class="collapse" aria-labelledby="headingVendor"
                                        data-parent="#accordionFlightStats">
                                        <div class="card-body p-0">
                                            @if (!empty($issuanceByBusinessPartner))
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($issuanceByBusinessPartner as $item)
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                            <span class="text-truncate"
                                                                title="{{ $item['bp_name'] ?? 'No Vendor' }}">
                                                                @if (!empty($item['bp_code']) && $item['bp_code'] !== '-')
                                                                    <span
                                                                        class="text-muted small">{{ $item['bp_code'] }}</span>
                                                                    —
                                                                @endif
                                                                {{ $item['bp_name'] ?? 'No Vendor' }}
                                                            </span>
                                                            <span
                                                                class="badge badge-primary badge-pill">{{ $item['count'] }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-muted px-3 py-2 mb-0 small">Belum ada data vendor.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Flight Requests & Recent Issuances -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Recent Flight Requests
                            </h3>
                            @can('flight-requests.show')
                                <div class="card-tools">
                                    <a href="{{ route('flight-requests.index') }}" class="btn btn-sm btn-primary">View
                                        All</a>
                                </div>
                            @endcan
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>FR Number</th>
                                            <th>Employee</th>
                                            <th class="text-center">Status</th>
                                            <th>Created</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentFlightRequests as $fr)
                                            <tr>
                                                <td><strong>{{ $fr->form_number ?? 'FR-' . substr($fr->id, 0, 8) }}</strong>
                                                </td>
                                                <td>{{ $fr->employee ? strtoupper($fr->employee->fullname ?? '-') : '-' }}
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $frBadges = [
                                                            'draft' => 'secondary',
                                                            'submitted' => 'info',
                                                            'approved' => 'primary',
                                                            'issued' => 'success',
                                                            'completed' => 'teal',
                                                            'rejected' => 'danger',
                                                            'cancelled' => 'warning',
                                                        ];
                                                        $status = $frBadges[$fr->status ?? ''] ?? 'secondary';
                                                    @endphp
                                                    <span
                                                        class="badge badge-{{ $status }}">{{ ucfirst($fr->status ?? '-') }}</span>
                                                </td>
                                                <td>{{ $fr->created_at ? $fr->created_at->format('d M Y') : '-' }}</td>
                                                <td class="text-center">
                                                    @can('flight-requests.show')
                                                        <a href="{{ route('flight-requests.show', $fr->id) }}"
                                                            class="btn btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No flight requests found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-signature mr-1"></i>
                                Recent Issuances (LG)
                            </h3>
                            @can('flight-issuances.show')
                                <div class="card-tools">
                                    <a href="{{ route('flight-issuances.index') }}" class="btn btn-sm btn-primary">View
                                        All</a>
                                </div>
                            @endcan
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Issued Number</th>
                                            <th>Business Partner</th>
                                            <th class="text-center">Status</th>
                                            <th>Created</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentIssuances as $iss)
                                            <tr>
                                                <td><strong>{{ $iss->issued_number ?? '-' }}</strong></td>
                                                <td>{{ $iss->businessPartner->bp_name ?? '-' }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $issBadges = [
                                                            'pending' => 'warning',
                                                            'approved' => 'success',
                                                            'rejected' => 'danger',
                                                        ];
                                                        $status = $issBadges[$iss->status ?? ''] ?? 'secondary';
                                                    @endphp
                                                    <span
                                                        class="badge badge-{{ $status }}">{{ ucfirst($iss->status ?? '-') }}</span>
                                                </td>
                                                <td>{{ $iss->created_at ? $iss->created_at->format('d M Y') : '-' }}</td>
                                                <td class="text-center">
                                                    @can('flight-issuances.show')
                                                        <a href="{{ route('flight-issuances.show', $iss->id) }}"
                                                            class="btn btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No issuances found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-bolt mr-1"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @can('flight-requests.show')
                                    <div class="col-md-4 mb-2">
                                        <a href="{{ route('flight-requests.index') }}" class="btn btn-primary btn-block">
                                            <i class="fas fa-list mr-1"></i> Flight Requests
                                        </a>
                                    </div>
                                @endcan
                                @can('flight-issuances.show')
                                    <div class="col-md-4 mb-2">
                                        <a href="{{ route('flight-issuances.index') }}" class="btn btn-info btn-block">
                                            <i class="fas fa-file-signature mr-1"></i> Issuances (LG)
                                        </a>
                                    </div>
                                @endcan
                                @can('flight-issuances.create')
                                    <div class="col-md-4 mb-2">
                                        <a href="{{ route('flight-issuances.select-flight-requests') }}"
                                            class="btn btn-success btn-block">
                                            <i class="fas fa-plus mr-1"></i> Create Letter of Guarantee
                                        </a>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .flight-stat-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            border: 1px solid #e9ecef;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .flight-stat-box:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .flight-stat-box .stat-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 0.5rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
        }

        .flight-stat-box .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #343a40;
            line-height: 1.2;
        }

        .flight-stat-box .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 2px;
        }

        .flight-stat-box.stat-total .stat-icon {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .flight-stat-box.stat-pending .stat-icon {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .flight-stat-box.stat-approved .stat-icon {
            background: linear-gradient(135deg, #17a2b8, #138496);
        }

        .flight-stat-box.stat-issued .stat-icon {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .flight-stat-box.stat-completed .stat-icon {
            background: linear-gradient(135deg, #20c997, #17a2b8);
        }

        .flight-stat-box.stat-month .stat-icon {
            background: linear-gradient(135deg, #001f3f, #0074d9);
        }

        .flight-stat-box.stat-lg-total .stat-icon {
            background: linear-gradient(135deg, #6610f2, #5a32a3);
        }

        .flight-stat-box.stat-lg-pending .stat-icon {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .flight-stat-box.stat-lg-approved .stat-icon {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .flight-stat-box.stat-lg-month .stat-icon {
            background: linear-gradient(135deg, #001f3f, #0074d9);
        }

        .progress-bar.bg-teal {
            background-color: #20c997 !important;
        }

        .progress-bar.bg-navy {
            background-color: #001f3f !important;
        }

        .progress-bar.bg-indigo {
            background-color: #6610f2 !important;
        }

        .badge-lg {
            font-size: 0.9rem;
            padding: 0.35em 0.6em;
        }

        .badge-teal {
            background-color: #20c997;
            color: #fff;
        }

        #accordionFlightStats .accordion-icon {
            transition: transform 0.2s ease;
        }

        #accordionFlightStats [aria-expanded="true"] .accordion-icon {
            transform: rotate(180deg);
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var issuanceByPartner = @json($issuanceByBusinessPartner ?? []);
            if (issuanceByPartner.length > 0) {
                var labels = issuanceByPartner.map(function(item) {
                    var code = item.bp_code && item.bp_code !== '-' ? item.bp_code + ' - ' : '';
                    return code + (item.bp_name || 'No Vendor');
                });
                var counts = issuanceByPartner.map(function(item) {
                    return item.count;
                });
                var colors = [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1',
                    '#20c997', '#fd7e14', '#e83e8c', '#17a2b8', '#6c757d',
                    '#6610f2', '#fd7e14', '#20c997', '#e83e8c'
                ];
                var bgColors = colors.slice(0, labels.length);

                var ctx = document.getElementById('lgByVendorChart');
                if (ctx) {
                    new Chart(ctx.getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: counts,
                                backgroundColor: bgColors,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 16,
                                        usePointStyle: true
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var label = context.label || '';
                                            var value = context.parsed || 0;
                                            var total = context.dataset.data.reduce(function(a, b) {
                                                return a + b;
                                            }, 0);
                                            var pct = total > 0 ? ((value / total) * 100).toFixed(1) :
                                                0;
                                            return label + ': ' + value + ' (' + pct + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
@endsection
