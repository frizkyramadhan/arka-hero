@extends('layouts.main')

@section('title', $title ?? 'Flight Reports')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle ?? 'Flight Reports' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Flight Reports</li>
                    </ol>
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
                                <i class="fas fa-plane"></i>
                                Flight Management Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Laporan detail tiket per LG (Letter of Guarantee): penumpang, rute, tanggal FR masuk & issued, target (selisih hari), nominal 622/151, harga, service charge, vendor.</p>
                            <p><strong>Fitur:</strong> Filter No. LG, Business Partner, rentang tanggal issued. Tabel sederhana, maks. 500 baris.</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('flight.reports.flight-management') }}" class="btn btn-primary">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar"></i>
                                FR Status Monitoring
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Monitor status Flight Request (draft, submitted, approved, issued, completed) dan progres approval.</p>
                            <p><strong>Fitur:</strong> Filter status, tanggal, project. (Coming soon)</p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-clock"></i> Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-invoice-dollar"></i>
                                LG Summary by Vendor / Period
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Ringkasan Letter of Guarantee per vendor dan periode: jumlah LG, total tiket, total nilai.</p>
                            <p><strong>Fitur:</strong> Group by vendor, filter periode, export. (Coming soon)</p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-clock"></i> Coming Soon
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building"></i>
                                Flight by Site / Project
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Analisis pemakaian tiket per site/project untuk kebutuhan perjalanan dinas.</p>
                            <p><strong>Fitur:</strong> Group by site, rute terbanyak, trend. (Coming soon)</p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-clock"></i> Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
