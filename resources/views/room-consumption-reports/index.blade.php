@extends('layouts.main')

@section('title', $title ?? 'Room & Consumption Reports')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle ?? 'Room & Consumption Reports' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Room & Consumption Reports</li>
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
                                <i class="fas fa-door-open"></i>
                                Room & Consumption Request Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Laporan permintaan ruang &amp; konsumsi: Reg. No, project, ruangan, judul meeting, tanggal/waktu, status, pemohon.</p>
                            <p><strong>Fitur:</strong> Filter status, project, rentang tanggal meeting, Reg. No, requester, room, judul. Klik <strong>Tampilkan data</strong> untuk memuat tabel, lalu ekspor Excel bila perlu.</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('room-consumption-requests.reports.request-monitoring') }}" class="btn btn-warning">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
