@extends('layouts.main')

@section('title', $title ?? 'Official Travel Reports')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle ?? 'Official Travel Reports' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Official Travel Reports</li>
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
                                <i class="fas fa-route"></i>
                                Official Travel Requests Report
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Laporan Letter of Travel (LOT): nomor LOT, tanggal, traveler (NIK &amp; nama), proyek asal, tujuan, maksud, durasi, transportasi, akomodasi, status, nomor surat, dan waktu dibuat.</p>
                            <p><strong>Fitur:</strong> Filter status (termasuk menunggu konfirmasi HR), proyek, rentang tanggal LOT, nomor LOT, tujuan, traveler (NIK/nama), teks pada maksud perjalanan. Tabel server-side (maks. 500 baris per halaman), ekspor Excel.</p>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('officialtravels.reports.travel-requests') }}" class="btn btn-primary">
                                <i class="fas fa-table"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
