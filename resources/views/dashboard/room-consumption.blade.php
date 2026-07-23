@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/fullcalendar/main.css') }}">
    <style>
        #rcr-calendar { min-height: 580px; }
        #rcr-calendar .fc-event { cursor: pointer; font-size: 0.78rem; border-radius: 3px; }
        #rcr-calendar .fc-toolbar-title { font-size: 1.05rem; font-weight: 600; }
        .rcr-stat-legend span { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 4px; vertical-align: middle; }
        .rcr-stat-legend { font-size: 0.75rem; }
        .rcr-info-box .progress { height: 3px; margin: 4px 0; }
        .rcr-info-box .info-box-icon { width: 50px; height: 50px; line-height: 50px; }
        .rcr-info-box .info-box-content { padding-left: 8px; }
        .rcr-info-box .info-box-number { font-size: 1.4rem; }
        .rcr-info-box .info-box-text { font-size: 0.9rem; }
        .rcr-info-box .progress-description { font-size: 0.75rem; }
        #rcr-cal-event-count { min-width: 28px; }
    </style>
@endsection

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

            {{-- Quick actions --}}
            <div class="row mb-2">
                <div class="col-12">
                    <div class="btn-group btn-group-sm flex-wrap">
                        @can('room-consumption-requests.show')
                            <a href="{{ route('room-consumption-requests.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list mr-1"></i> Semua Request
                            </a>
                        @endcan
                        @can('room-consumption-requests.create')
                            <a href="{{ route('room-consumption-requests.create') }}" class="btn btn-outline-success">
                                <i class="fas fa-plus mr-1"></i> Buat Request
                            </a>
                        @endcan
                        @can('meeting-rooms.show')
                            <a href="{{ route('meeting-rooms.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-door-open mr-1"></i> Master Ruangan
                            </a>
                        @endcan
                        @can('room-consumption-requests.show')
                            <a href="{{ route('room-consumption-requests.reports.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-chart-pie mr-1"></i> Laporan
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Row 1: primary stats --}}
            <div class="row mb-2">
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-primary mb-0 rcr-info-box">
                        <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Request</span>
                            <span class="info-box-number">{{ number_format($totalRequests) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                +{{ number_format($thisMonthCreated) }} bulan ini
                                @if ($createdMonthGrowthPct != 0)
                                    ({{ $createdMonthGrowthPct >= 0 ? '+' : '' }}{{ $createdMonthGrowthPct }}%)
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-success mb-0 rcr-info-box">
                        <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Meeting Bulan Ini</span>
                            <span class="info-box-number">{{ number_format($thisMonthMeetings) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $totalRequests > 0 ? min(100, ($thisMonthMeetings / $totalRequests) * 100) : 0 }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ number_format($meetingsToday) }} hari ini · {{ number_format($meetingsThisWeek) }} minggu ini
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-info mb-0 rcr-info-box">
                        <span class="info-box-icon"><i class="fas fa-door-open"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Meeting Room</span>
                            <span class="info-box-number">{{ number_format($activeRooms) }}/{{ number_format($totalRooms) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $totalRooms > 0 ? ($activeRooms / $totalRooms) * 100 : 0 }}%"></div>
                            </div>
                            <span class="progress-description">Ruangan aktif / total master</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-warning mb-0 rcr-info-box">
                        <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Perlu Tindakan</span>
                            <span class="info-box-number">{{ number_format($pendingApprovalSteps + $needZoomPending) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $totalRequests > 0 ? min(100, (($pendingApprovalSteps + $needZoomPending) / $totalRequests) * 100) : 0 }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ number_format($pendingApprovalSteps) }} approval · {{ number_format($needZoomPending) }} zoom pending
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: zoom & consumption --}}
            <div class="row mb-3">
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box mb-0 rcr-info-box">
                        <span class="info-box-icon bg-purple elevation-1"><i class="fas fa-video"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Butuh Zoom</span>
                            <span class="info-box-number">{{ number_format($needZoomTotal) }}</span>
                            <span class="progress-description text-muted">Submitted / approved</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box mb-0 rcr-info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-double"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Zoom Siap</span>
                            <span class="info-box-number">{{ number_format($needZoomReady) }}</span>
                            <span class="progress-description text-muted">Meeting ID tersedia</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box mb-0 rcr-info-box">
                        <span class="info-box-icon bg-orange elevation-1"><i class="fas fa-utensils"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Dengan Konsumsi</span>
                            <span class="info-box-number">{{ number_format($withConsumption) }}</span>
                            <span class="progress-description text-muted">Meeting aktif + konsumsi</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box mb-0 rcr-info-box">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Menunggu Approval</span>
                            <span class="info-box-number">{{ number_format($pendingApprovalSteps) }}</span>
                            <span class="progress-description text-muted">Langkah approval terbuka</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Calendar + sidebar --}}
            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="card card-outline card-primary mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-calendar-alt mr-1"></i> Kalender Meeting
                                <span id="rcr-cal-event-count" class="badge badge-primary ml-1">0</span>
                            </h3>
                            <div class="card-tools d-flex align-items-center flex-wrap" style="gap: 6px;">
                                <select id="rcr-cal-room" class="form-control form-control-sm" style="width: auto; max-width: 160px;">
                                    <option value="">Semua ruangan</option>
                                    @foreach ($calendarRooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->room_name }}</option>
                                    @endforeach
                                </select>
                                <select id="rcr-cal-month" class="form-control form-control-sm" style="width: auto;">
                                    @foreach ([1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'] as $m => $label)
                                        <option value="{{ $m }}" @selected($m == now()->month)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select id="rcr-cal-year" class="form-control form-control-sm" style="width: auto;">
                                    @for ($y = now()->year - 1; $y <= now()->year + 2; $y++)
                                        <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                                    @endfor
                                </select>
                                <button type="button" id="rcr-cal-today" class="btn btn-sm btn-outline-secondary">Hari ini</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-2 rcr-stat-legend text-muted">
                                <span style="background:#17a2b8"></span> Submitted
                                <span class="ml-3" style="background:#28a745"></span> Approved
                                <span class="ml-3" style="background:#007bff"></span> Completed
                            </div>
                            <div id="rcr-calendar"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm mb-0"><i class="fas fa-door-closed mr-1"></i> Top Ruangan</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    @forelse ($byRoom as $row)
                                        <tr>
                                            <td>{{ $row->room_name }}</td>
                                            <td class="text-right"><span class="badge badge-light">{{ $row->request_count }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-muted text-center py-3">Belum ada data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm mb-0"><i class="fas fa-project-diagram mr-1"></i> Top Project</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    @forelse ($byProject as $row)
                                        <tr>
                                            <td>{{ $row->project_code }}</td>
                                            <td class="text-muted small">{{ Str::limit($row->project_name, 22) }}</td>
                                            <td class="text-right"><span class="badge badge-light">{{ $row->request_count }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-muted text-center py-3">Belum ada data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm mb-0"><i class="fas fa-clock mr-1"></i> Meeting Mendatang</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse ($upcomingMeetings as $req)
                                    <li class="list-group-item py-2">
                                        <a href="{{ route('room-consumption-requests.show', $req) }}" class="font-weight-bold">
                                            {{ Str::limit($req->meeting_title, 30) }}
                                        </a>
                                        @if ($req->need_zoom)
                                            <i class="fas fa-video text-purple ml-1" title="Butuh Zoom"></i>
                                        @endif
                                        <div class="small text-muted">
                                            {{ $req->meeting_date ? format_date_with_weekday($req->meeting_date) : '—' }}
                                            · {{ $req->start_time ? \Carbon\Carbon::parse($req->start_time)->format('H:i') : '—' }}
                                            · {{ $req->meetingRoom->room_name ?? '—' }}
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted small">Tidak ada meeting mendatang</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-0">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title text-sm mb-0"><i class="fas fa-history mr-1"></i> Request Terbaru</h3>
                            <a href="{{ route('room-consumption-requests.index') }}" class="btn btn-tool btn-sm">Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse ($recentRequests as $req)
                                    <li class="list-group-item py-2">
                                        <a href="{{ route('room-consumption-requests.show', $req) }}">
                                            {{ Str::limit($req->request_number ?: $req->meeting_title, 28) }}
                                        </a>
                                        <span class="badge badge-{{ ['draft'=>'secondary','submitted'=>'info','approved'=>'success','rejected'=>'danger','cancelled'=>'dark','completed'=>'primary'][$req->status] ?? 'secondary' }} float-right">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                        <div class="small text-muted">{{ $req->created_at?->format('d/m/Y H:i') }}</div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted small">Belum ada request</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/fullcalendar/main.js') }}"></script>
    <script src="{{ asset('assets/plugins/fullcalendar/locales/id.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('rcr-calendar');
            var monthSel = document.getElementById('rcr-cal-month');
            var yearSel = document.getElementById('rcr-cal-year');
            var roomSel = document.getElementById('rcr-cal-room');
            var eventCountEl = document.getElementById('rcr-cal-event-count');
            var eventsUrl = @json(route('dashboard.room-consumption.calendar-events'));
            var syncingFilters = false;

            function updateEventCount() {
                var events = calendar.getEvents();
                eventCountEl.textContent = events.length;
            }

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'id',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                height: 'auto',
                navLinks: true,
                editable: false,
                dayMaxEvents: 3,
                moreLinkClick: 'popover',
                events: {
                    url: eventsUrl,
                    extraParams: function() {
                        return {
                            room_id: roomSel.value || ''
                        };
                    },
                    failure: function() {
                        if (typeof toast_error === 'function') {
                            toast_error('Gagal memuat kalender meeting.');
                        }
                    }
                },
                loading: function(isLoading) {
                    calendarEl.style.opacity = isLoading ? '0.55' : '1';
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                },
                eventDidMount: function(info) {
                    var p = info.event.extendedProps;
                    var lines = [
                        p.requestNumber ? 'No: ' + p.requestNumber : null,
                        p.room ? 'Ruangan: ' + p.room : null,
                        p.project ? 'Project: ' + p.project : null,
                        p.requester ? 'Requester: ' + p.requester : null,
                        'Status: ' + (p.status || '—'),
                        p.needZoom ? 'Zoom: Ya' : null
                    ].filter(Boolean);
                    info.el.setAttribute('title', lines.join('\n'));
                },
                eventsSet: function() {
                    updateEventCount();
                }
            });

            calendar.render();

            function gotoFilterMonth() {
                var m = parseInt(monthSel.value, 10);
                var y = parseInt(yearSel.value, 10);
                syncingFilters = true;
                calendar.gotoDate(new Date(y, m - 1, 1));
                syncingFilters = false;
                calendar.refetchEvents();
            }

            monthSel.addEventListener('change', gotoFilterMonth);
            yearSel.addEventListener('change', gotoFilterMonth);
            roomSel.addEventListener('change', function() {
                calendar.refetchEvents();
            });

            document.getElementById('rcr-cal-today').addEventListener('click', function() {
                var now = new Date();
                monthSel.value = now.getMonth() + 1;
                yearSel.value = now.getFullYear();
                calendar.today();
                calendar.refetchEvents();
            });

            calendar.on('datesSet', function() {
                if (syncingFilters) {
                    return;
                }
                var d = calendar.getDate();
                monthSel.value = d.getMonth() + 1;
                yearSel.value = d.getFullYear();
            });
        });
    </script>
@endpush
