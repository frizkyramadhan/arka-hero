@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Letter Number Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('letter-numbers.index') }}">Letter Administration</a>
                        </li>
                        <li class="breadcrumb-item active">Letter Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- Letter Number Information -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Letter Number Information</h3>
                            <div class="card-tools">
                                @php
                                    $statusBadge = [
                                        'reserved' => 'badge-warning',
                                        'used' => 'badge-success',
                                        'cancelled' => 'badge-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusBadge[$letterNumber->status] ?? 'badge-secondary' }} badge-lg">
                                    {{ strtoupper($letterNumber->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Letter Number</strong></td>
                                            <td>: {{ $letterNumber->letter_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Category</strong></td>
                                            <td>: {{ $letterNumber->category->category_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Letter Date</strong></td>
                                            <td>:
                                                {{ $letterNumber->letter_date ? $letterNumber->letter_date->format('d/m/Y') : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Subject</strong></td>
                                            <td>:
                                                {{ $letterNumber->subject->subject_name ?? ($letterNumber->custom_subject ?? '-') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Destination</strong></td>
                                            <td>: {{ $letterNumber->destination ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Created By</strong></td>
                                            <td>:
                                                {{ $letterNumber->reservedBy->name ?? ($letterNumber->user->name ?? '-') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created Date</strong></td>
                                            <td>: {{ $letterNumber->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @if ($letterNumber->used_at)
                                            <tr>
                                                <td><strong>Used At</strong></td>
                                                <td>: {{ $letterNumber->used_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Used By</strong></td>
                                                <td>: {{ $letterNumber->usedBy->name ?? '-' }}</td>
                                            </tr>
                                        @endif
                                        @if ($letterNumber->remarks)
                                            <tr>
                                                <td><strong>Remarks</strong></td>
                                                <td>: {{ $letterNumber->remarks }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Data (if exists) -->
                    @if ($letterNumber->administration)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Employee Data</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="40%"><strong>NIK</strong></td>
                                                <td>: {{ $letterNumber->administration->nik }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Full Name</strong></td>
                                                <td>: {{ $letterNumber->administration->employee->fullname ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Project</strong></td>
                                                <td>: {{ $letterNumber->administration->project->project_name ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Position</strong></td>
                                                <td>: {{ $letterNumber->administration->position->position_name ?? '-' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="40%"><strong>DOH</strong></td>
                                                <td>:
                                                    {{ $letterNumber->administration->doh ? \Carbon\Carbon::parse($letterNumber->administration->doh)->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Status</strong></td>
                                                <td>:
                                                    @if ($letterNumber->administration->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Category Specific Data -->
                    @if ($letterNumber->category->category_code === 'PKWT' && ($letterNumber->pkwt_type || $letterNumber->duration))
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">PKWT Data</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="40%"><strong>PKWT Type</strong></td>
                                                <td>: {{ $letterNumber->pkwt_type ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Duration</strong></td>
                                                <td>: {{ $letterNumber->duration ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td width="40%"><strong>Start Date</strong></td>
                                                <td>:
                                                    {{ $letterNumber->start_date ? $letterNumber->start_date->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>End Date</strong></td>
                                                <td>:
                                                    {{ $letterNumber->end_date ? $letterNumber->end_date->format('d/m/Y') : '-' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($letterNumber->category->category_code === 'PAR' && $letterNumber->par_type)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">PAR Data</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="20%"><strong>PAR Type</strong></td>
                                        <td>: {{ ucfirst($letterNumber->par_type) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if (in_array($letterNumber->category->category_code, ['A', 'B']) && $letterNumber->classification)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Letter Classification</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="20%"><strong>Classification</strong></td>
                                        <td>: {{ $letterNumber->classification }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if (in_array($letterNumber->category->category_code, ['FR']) && $letterNumber->ticket_classification)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Ticket Classification</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="20%"><strong>Ticket Classification</strong></td>
                                        <td>: {{ $letterNumber->ticket_classification }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Integration Info -->
                    @if ($letterNumber->related_document_type && $letterNumber->related_document_id)
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Integration Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <h5><i class="icon fas fa-check"></i> Letter Number Already Used</h5>
                                    <p>This letter number has been used for document:</p>
                                    <ul class="mb-0">
                                        <li><strong>Document Type:</strong>
                                            {{ ucfirst($letterNumber->related_document_type) }}</li>
                                        <li><strong>Document ID:</strong> {{ $letterNumber->related_document_id }}</li>
                                        @if ($letterNumber->used_at)
                                            <li><strong>Usage Date:</strong>
                                                {{ $letterNumber->used_at->format('d/m/Y H:i') }}</li>
                                        @endif
                                    </ul>
                                </div>

                                @php
                                    $documentLink = '#';
                                    $documentName = 'Document';
                                    switch ($letterNumber->related_document_type) {
                                        case 'officialtravel':
                                            $documentLink = route(
                                                'officialtravels.show',
                                                $letterNumber->related_document_id,
                                            );
                                            $documentName = 'Official Travel Letter';
                                            break;
                                        case 'recruitment_request':
                                            $documentLink = route(
                                                'recruitment.requests.show',
                                                $letterNumber->related_document_id,
                                            );
                                            $documentName = 'Recruitment Request';
                                            break;
                                    }
                                @endphp

                                <a href="{{ $documentLink }}" class="btn btn-success">
                                    <i class="fas fa-external-link-alt"></i> View {{ $documentName }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <!-- Action Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('letter-numbers.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>

                            @if ($letterNumber->status === 'reserved')
                                <a href="{{ route('letter-numbers.edit', $letterNumber->id) }}"
                                    class="btn btn-warning btn-block">
                                    <i class="fas fa-edit"></i> Edit Data
                                </a>

                                <button type="button" class="btn btn-secondary btn-block btn-cancel"
                                    data-id="{{ $letterNumber->id }}"
                                    data-letter-number="{{ $letterNumber->letter_number }}">
                                    <i class="fas fa-ban"></i> Cancel Number
                                </button>

                                @if (!$letterNumber->related_document_id)
                                    <form action="{{ route('letter-numbers.mark-as-used-manually', $letterNumber->id) }}"
                                        method="POST" class="d-block mt-2"
                                        onsubmit="return confirm('Are you sure you want to manually mark this letter number as used?');">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-check-circle"></i> Mark as Used (Manual)
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-danger btn-block btn-delete mt-2"
                                        data-id="{{ $letterNumber->id }}"
                                        data-letter-number="{{ $letterNumber->letter_number }}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Status Information</h3>
                        </div>
                        <div class="card-body">
                            @if ($letterNumber->status === 'reserved')
                                <div class="alert alert-warning">
                                    <h6><i class="icon fas fa-exclamation-triangle"></i> Status: Reserved</h6>
                                    <p class="mb-0">This letter number is reserved and ready to be used for creating
                                        documents.</p>
                                </div>
                            @elseif($letterNumber->status === 'used')
                                <div class="alert alert-success">
                                    <h6><i class="icon fas fa-check"></i> Status: Used</h6>
                                    <p class="mb-0">This letter number has been used for a document and cannot be changed
                                        anymore.</p>
                                </div>
                            @elseif($letterNumber->status === 'cancelled')
                                <div class="alert alert-danger">
                                    <h6><i class="icon fas fa-ban"></i> Status: Cancelled</h6>
                                    <p class="mb-0">This letter number has been cancelled and cannot be used.</p>
                                </div>
                            @endif

                            @if ($letterNumber->status === 'reserved' && !$letterNumber->related_document_id)
                                <div class="alert alert-info">
                                    <h6><i class="icon fas fa-info"></i> How to Use</h6>
                                    <p class="mb-0">To use this number, create a new document in the related system and
                                        select this number from the dropdown.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

    <script>
        $(function() {
            // Cancel function
            $('.btn-cancel').click(function() {
                var id = $(this).data('id');
                var letterNumber = $(this).data('letter-number');

                Swal.fire({
                    title: 'Confirm Cancel',
                    text: 'Are you sure you want to cancel letter number ' + letterNumber + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f39c12',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Cancel!',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/api/v1/letter-numbers/' + id + '/cancel',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var response = JSON.parse(xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message ||
                                        'An error occurred while cancelling the letter number',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });

            // Delete function
            $('.btn-delete').click(function() {
                var id = $(this).data('id');
                var letterNumber = $(this).data('letter-number');

                Swal.fire({
                    title: 'Confirm Delete',
                    text: 'Are you sure you want to delete letter number ' + letterNumber + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Delete!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('letter-numbers.destroy', ':id') }}'.replace(
                                ':id', id),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6'
                                    }).then(() => {
                                        window.location.href =
                                            '{{ route('letter-numbers.index') }}';
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var response = JSON.parse(xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message ||
                                        'An error occurred while deleting the letter number',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
