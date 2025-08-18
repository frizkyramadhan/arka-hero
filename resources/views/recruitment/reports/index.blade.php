@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle }}</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-funnel-dollar"></i>
                                Recruitment Funnel by Stage
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Track candidate progression through recruitment stages (CV Review, Interview, Psikotes, Tes
                                Teori, MCU, Offering, Hiring, Onboarding).</p>
                            <p><strong>Features:</strong> Stage counts, date filtering, Excel export</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('recruitment.reports.funnel') }}" class="btn btn-primary">
                                <i class="fas fa-chart-bar"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock"></i>
                                Request Aging & SLA
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Monitor FPTK request processing times, approval bottlenecks, and SLA compliance.</p>
                            <p><strong>Features:</strong> Days open tracking, approval timelines, department/project filters
                            </p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('recruitment.reports.aging') }}" class="btn btn-primary">
                                <i class="fas fa-stopwatch"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-check"></i>
                                Time-to-Hire Analysis
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Measure recruitment efficiency from request creation to candidate onboarding.</p>
                            <p><strong>Features:</strong> Total days, approval days, recruitment days, Excel export</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('recruitment.reports.time-to-hire') }}" class="btn btn-success">
                                <i class="fas fa-hourglass-half"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-handshake"></i>
                                Offer Acceptance Rate
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Track offer acceptance/rejection rates and response times by department and position.</p>
                            <p><strong>Features:</strong> Acceptance rates, response times, Excel export</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('recruitment.reports.offer-acceptance-rate') }}" class="btn btn-info">
                                <i class="fas fa-percentage"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-check"></i>
                                Interview & Assessment Analytics
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Analyze interview scheduling efficiency and assessment pass/fail rates.</p>
                            <p><strong>Status:</strong> <span class="badge badge-warning">Coming Soon</span></p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-chart-pie"></i> Coming Soon
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle"></i>
                                Stale Candidates Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Identify candidates with no progress for extended periods (7/14/30+ days).</p>
                            <p><strong>Status:</strong> <span class="badge badge-warning">Coming Soon</span></p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-user-clock"></i> Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
