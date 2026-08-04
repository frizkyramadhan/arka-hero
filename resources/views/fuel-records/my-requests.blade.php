@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">{{ $title }}</h1></div>
                <div class="col-sm-6 text-right">
                    @can('personal.fuel.create-own')
                        <a href="{{ route('fuel-records.my-requests.create') }}" class="btn btn-warning">
                            <i class="fas fa-camera"></i> Log fuel
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid" style="max-width: 720px">
            <p class="text-muted">{{ $subtitle }}</p>
            @forelse ($records as $r)
                <a href="{{ route('fuel-records.my-requests.show', $r) }}" class="card card-outline mb-2 text-dark" style="text-decoration:none">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between">
                            <strong>{{ optional($r->fuel_date)->format('d M Y') }}</strong>
                            @php
                                $map = ['submitted'=>'warning','verified'=>'success','rejected'=>'danger','claimed'=>'info'];
                            @endphp
                            <span class="badge badge-{{ $map[$r->status] ?? 'secondary' }}">{{ ucfirst($r->status) }}</span>
                        </div>
                        <div>{{ $r->vehicle?->kode }} — {{ $r->vehicle?->license_plate }}</div>
                        <div class="small text-muted">
                            {{ $r->fuel_type }} · {{ number_format((float)$r->quantity, 2) }} L ·
                            Rp {{ number_format((float)$r->total_cost, 0, ',', '.') }}
                        </div>
                    </div>
                </a>
            @empty
                <div class="alert alert-light border">No fuel logs yet. Tap <strong>Log fuel</strong> to scan a receipt.</div>
            @endforelse
            {{ $records->links() }}
        </div>
    </section>
@endsection
