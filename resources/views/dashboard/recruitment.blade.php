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
            <!-- Statistics Cards (Info Boxes) -->
            <div class="row mb-4">
                <!-- Active/Approved FPTK -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-primary">
                        <span class="info-box-icon"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active/Approved FPTK</span>
                            <span class="info-box-number">{{ $stats['active_fptk'] ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">All requests currently active</span>
                        </div>
                    </div>
                </div>

                <!-- Candidate Pool (Available/In Process) -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-success">
                        <span class="info-box-icon"><i class="fas fa-user-friends"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Candidate Pool</span>
                            <span class="info-box-number">{{ $stats['candidate_pool'] ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">Available or in process</span>
                        </div>
                    </div>
                </div>

                <!-- Total Sessions -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-warning">
                        <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Sessions</span>
                            <span class="info-box-number">{{ $stats['total_sessions'] ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">All time applications</span>
                        </div>
                    </div>
                </div>

                <!-- New Applications (This Month) -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">New Applications</span>
                            <span class="info-box-number">{{ $stats['sessions_this_month'] ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">Applications in {{ date('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics -->
            <div class="row">
                <!-- Sessions by Stage Chart -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Active Sessions by Stage
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="sessionsByStageChart"
                                style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i>
                                Quick Statistics
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Success Rate</span>
                                    <span class="info-box-number">
                                        @php
                                            $successRate =
                                                $stats['total_sessions'] > 0
                                                    ? round(
                                                        ($stats['completed_sessions'] / $stats['total_sessions']) * 100,
                                                        1,
                                                    )
                                                    : 0;
                                        @endphp
                                        {{ $successRate }}%
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: {{ $successRate }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Active Rate</span>
                                    <span class="info-box-number">
                                        @php
                                            $activeRate =
                                                $stats['total_sessions'] > 0
                                                    ? round(
                                                        ($stats['active_sessions'] / $stats['total_sessions']) * 100,
                                                        1,
                                                    )
                                                    : 0;
                                        @endphp
                                        {{ $activeRate }}%
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: {{ $activeRate }}%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rejection Rate</span>
                                    <span class="info-box-number">
                                        @php
                                            $rejectionRate =
                                                $stats['total_sessions'] > 0
                                                    ? round(
                                                        ($stats['rejected_sessions'] / $stats['total_sessions']) * 100,
                                                        1,
                                                    )
                                                    : 0;
                                        @endphp
                                        {{ $rejectionRate }}%
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-danger" style="width: {{ $rejectionRate }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sessions and Stage Breakdown -->
            <div class="row">
                <!-- Recent Sessions -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Recent Sessions
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('recruitment.sessions.index') }}" class="btn btn-sm btn-primary">
                                    View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="align-middle">FPTK Number</th>
                                            <th class="align-middle">Position</th>
                                            <th class="align-middle">Candidate</th>
                                            <th class="align-middle">Stage</th>
                                            <th class="align-middle text-center">Status</th>
                                            <th class="align-middle">Applied Date</th>
                                            <th class="align-middle text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentSessions as $session)
                                            <tr>
                                                <td>
                                                    <strong>{{ $session->fptk->request_number ?? 'N/A' }}</strong>
                                                </td>
                                                <td>{{ $session->fptk->position->position_name ?? 'N/A' }}</td>
                                                <td>{{ $session->candidate->fullname ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $stages = [
                                                            'cv_review' => 'CV Review',
                                                            'psikotes' => 'Psikotes',
                                                            'tes_teori' => 'Tes Teori',
                                                            'interview_hr' => 'Interview HR',
                                                            'interview_user' => 'Interview User',
                                                            'offering' => 'Offering',
                                                            'mcu' => 'MCU',
                                                            'hire' => 'Hire',
                                                            'onboarding' => 'Onboarding',
                                                        ];
                                                    @endphp
                                                    {{ $stages[$session->current_stage] ?? $session->current_stage }}
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $statusBadges = [
                                                            'in_process' =>
                                                                '<span class="badge badge-primary">In Process</span>',
                                                            'hired' => '<span class="badge badge-success">Hired</span>',
                                                            'rejected' =>
                                                                '<span class="badge badge-danger">Rejected</span>',
                                                            'withdrawn' =>
                                                                '<span class="badge badge-secondary">Withdrawn</span>',
                                                            'cancelled' =>
                                                                '<span class="badge badge-warning">Cancelled</span>',
                                                        ];
                                                    @endphp
                                                    {!! $statusBadges[$session->status] ??
                                                        '<span class="badge badge-secondary">' . ucfirst($session->status) . '</span>' !!}
                                                </td>
                                                <td>{{ $session->applied_date ? $session->applied_date->format('d M Y') : 'N/A' }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('recruitment.sessions.candidate', $session->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No recent sessions found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stage Breakdown -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group mr-1"></i>
                                Stage Breakdown
                            </h3>
                        </div>
                        <div class="card-body">
                            @forelse($sessionsByStage as $stageData)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        @php
                                            $stageNames = [
                                                'cv_review' => 'CV Review',
                                                'psikotes' => 'Psikotes',
                                                'tes_teori' => 'Tes Teori',
                                                'interview_hr' => 'Interview HR',
                                                'interview_user' => 'Interview User',
                                                'offering' => 'Offering',
                                                'mcu' => 'MCU',
                                                'hire' => 'Hire',
                                                'onboarding' => 'Onboarding',
                                            ];
                                        @endphp
                                        <span
                                            class="text-sm">{{ $stageNames[$stageData->current_stage] ?? $stageData->current_stage }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-primary mr-2">{{ $stageData->count }}</span>
                                        <div class="progress flex-grow-1" style="width: 60px; height: 6px;">
                                            @php
                                                $percentage =
                                                    $stats['active_sessions'] > 0
                                                        ? round(($stageData->count / $stats['active_sessions']) * 100)
                                                        : 0;
                                            @endphp
                                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted text-center">No active sessions found</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt mr-1"></i>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('recruitment.sessions.index') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-list mr-1"></i> View All Sessions
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <style>
        .bg-birthday {
            background-color: #e91e63 !important;
        }

        .info-box {
            margin-bottom: 15px;
        }

        .info-box-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.875rem;
            color: white;
        }

        .info-box-content {
            padding: 15px;
            flex: 1;
        }

        .info-box-text {
            display: block;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-box-number {
            display: block;
            font-weight: bold;
            font-size: 18px;
        }

        .progress {
            height: 3px;
            margin-top: 5px;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Sessions by Stage Chart
            var stageData = @json($sessionsByStage);
            var stageLabels = [];
            var stageCounts = [];
            var stageColors = [
                '#007bff', '#28a745', '#ffc107', '#dc3545',
                '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#6c757d'
            ];

            stageData.forEach(function(item, index) {
                var stageNames = {
                    'cv_review': 'CV Review',
                    'psikotes': 'Psikotes',
                    'tes_teori': 'Tes Teori',
                    'interview_hr': 'Interview HR',
                    'interview_user': 'Interview User',
                    'offering': 'Offering',
                    'mcu': 'MCU',
                    'hire': 'Hire',
                    'onboarding': 'Onboarding'
                };

                stageLabels.push(stageNames[item.current_stage] || item.current_stage);
                stageCounts.push(item.count);
            });

            var ctx = document.getElementById('sessionsByStageChart').getContext('2d');
            var sessionsByStageChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: stageLabels,
                    datasets: [{
                        data: stageCounts,
                        backgroundColor: stageColors.slice(0, stageLabels.length),
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
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.parsed || 0;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
