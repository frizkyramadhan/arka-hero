@extends('layouts.main')

@section('title', 'Bulk Leave Requests')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Periodic Leave Requests</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Periodic Leave Requests</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        <strong>Periodic Leave Request Batches</strong>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('leave.bulk-requests.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Create Periodic Leave Request
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($batches->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No periodic leave requests found</h5>
                            <p class="text-muted">Create your first periodic leave request</p>
                            <a href="{{ route('leave.bulk-requests.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus mr-1"></i> Create Periodic Leave Request
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Batch ID</th>
                                        <th>Total Requests</th>
                                        <th>Notes</th>
                                        <th>Created At</th>
                                        <th width="100" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($batches as $index => $batch)
                                        <tr>
                                            <td>{{ $batches->firstItem() + $index }}</td>
                                            <td>
                                                <a href="{{ route('leave.bulk-requests.show', $batch->batch_id) }}"
                                                    class="text-primary font-weight-bold">
                                                    <i class="fas fa-layer-group mr-1"></i>
                                                    {{ $batch->batch_id }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-info badge-lg">
                                                    {{ $batch->total_requests }} requests
                                                </span>
                                            </td>
                                            <td>
                                                @if ($batch->bulk_notes)
                                                    <small class="text-muted">
                                                        {{ Str::limit($batch->bulk_notes, 50) }}
                                                    </small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ $batch->created_at->format('d M Y') }}
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ $batch->created_at->format('H:i') }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('leave.bulk-requests.show', $batch->batch_id) }}"
                                                    class="btn btn-sm btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $batches->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .badge-lg {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }
    </style>
@endsection
