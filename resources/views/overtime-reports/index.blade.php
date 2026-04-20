@extends('layouts.main')

@section('title', $title ?? 'Overtime Reports')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle ?? 'Overtime Reports' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Overtime Reports</li>
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
                                <i class="fas fa-business-time"></i>
                                Overtime Request Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Laporan permintaan lembur per header: nomor register (YYOT-xxxxx), proyek, tanggal lembur, status, pemohon, daftar karyawan di detail, remarks.</p>
                            <p><strong>Fitur:</strong> Status &amp; project memiliki opsi <em>Select</em> (belum memilih), <em>All</em> (semua), atau nilai spesifik; ditambah filter tanggal OT, nomor register, pemohon, karyawan, remarks. Klik <strong>Tampilkan data</strong> untuk memuat tabel, lalu ekspor Excel bila perlu.</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('overtime.reports.request-monitoring') }}" class="btn btn-warning">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
