@extends('layouts.main')

@section('title', $title)

@section('content')
    @php
        $statusMap = [
            'active' => ['label' => 'Active', 'class' => 'overtime-pill-approved', 'icon' => 'fa-check-circle'],
            'inactive' => ['label' => 'Inactive', 'class' => 'overtime-pill-draft', 'icon' => 'fa-ban'],
            'maintenance' => ['label' => 'Maintenance', 'class' => 'overtime-pill-pending', 'icon' => 'fa-tools'],
            'sold' => ['label' => 'Sold', 'class' => 'overtime-pill-finished', 'icon' => 'fa-handshake'],
            'accident' => ['label' => 'Accident', 'class' => 'overtime-pill-rejected', 'icon' => 'fa-car-crash'],
        ];
        $pill = $statusMap[$vehicle->status] ?? [
            'label' => ucfirst($vehicle->status),
            'class' => 'overtime-pill-draft',
            'icon' => 'fa-question-circle',
        ];
        $brandModel = trim(($vehicle->brand ?? '').' '.($vehicle->model ?? ''));
        $validityTypes = ['stnk' => 'STNK & Plate', 'pkb' => 'PKB', 'kir' => 'KIR'];
    @endphp

    @include('partials.official-travel-detail-styles')

    <style>
        .vehicle-fuel-btn {
            background-color: #27ae60;
        }

        .vehicle-fuel-btn:hover {
            color: white;
        }

        .vehicle-delete-btn {
            background-color: #dc3545;
        }

        .vehicle-delete-btn:hover {
            color: white;
        }

        .vehicle-validity-item + .vehicle-validity-item {
            border-top: 1px solid #eef2f5;
            margin-top: 0.85rem;
            padding-top: 0.85rem;
        }
    </style>

    <div class="content-wrapper-custom">
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">
                    {{ $vehicle->lokasi ?: 'Vehicle' }}
                </div>
                <h1 class="travel-destination">
                    {{ $vehicle->kode }} — {{ $vehicle->license_plate }}
                </h1>
                <div class="travel-date">
                    <i class="fas fa-user mr-1"></i>
                    {{ $vehicle->pic ?: 'No PIC assigned' }}
                    <span class="mx-2">·</span>
                    <i class="fas fa-tachometer-alt mr-1"></i>
                    {{ number_format((int) $vehicle->odometer) }} km
                    @if ($brandModel !== '')
                        <span class="mx-2">·</span>
                        <i class="fas fa-industry mr-1"></i>
                        {{ $brandModel }}
                    @endif
                </div>
            </div>
            <div class="travel-status-pill">
                <span class="overtime-status-pill {{ $pill['class'] }}">
                    <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                </span>
            </div>
        </div>

        <div class="travel-content">
            <div class="row">
                {{-- Left Column --}}
                <div class="col-lg-8">
                    <div class="travel-card travel-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-car"></i> Vehicle Details</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-barcode"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Code</div>
                                        <div class="info-value">{{ $vehicle->kode }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">License Plate</div>
                                        <div class="info-value">{{ $vehicle->license_plate }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #8e44ad;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">PIC</div>
                                        <div class="info-value">{{ $vehicle->pic ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2ecc71;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Location</div>
                                        <div class="info-value">{{ $vehicle->lokasi ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-align-left"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Description</div>
                                        <div class="info-value">{{ $vehicle->description ?: '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #34495e;">
                                        <i class="fas fa-industry"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Brand / Model</div>
                                        <div class="info-value">{{ $brandModel !== '' ? $brandModel : '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f39c12;">
                                        <i class="fas fa-car-side"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Type</div>
                                        <div class="info-value">{{ ucfirst($vehicle->type ?? '—') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #16a085;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Ownership</div>
                                        <div class="info-value">{{ ucfirst($vehicle->ownership ?? '—') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-gas-pump"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Fuel Type</div>
                                        <div class="info-value">{{ $vehicle->fuel_type ? ucfirst($vehicle->fuel_type) : '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #7f8c8d;">
                                        <i class="fas fa-tachometer-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Odometer</div>
                                        <div class="info-value">{{ number_format((int) $vehicle->odometer) }} km</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2980b9;">
                                        <i class="fas fa-cloud"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">ArkFleet Status</div>
                                        <div class="info-value">{{ $vehicle->arkfleet_status ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            @if ($vehicle->keterangan)
                                <div class="overtime-remarks-block border-top">
                                    <div class="info-item overtime-remarks-item">
                                        <div class="info-icon" style="background-color: #1abc9c;">
                                            <i class="fas fa-comment-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Remarks</div>
                                            <div class="info-value overtime-remarks-value">{{ $vehicle->keterangan }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="travel-card">
                        <div class="card-head d-flex align-items-center justify-content-between">
                            <h2 class="mb-0"><i class="fas fa-folder-open"></i> Documents</h2>
                            @can('vehicle-documents.create')
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#modal-add-document">
                                    <i class="fas fa-plus"></i> Add Document
                                </button>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Document No.</th>
                                            <th>Issue Date</th>
                                            <th>Expiry</th>
                                            <th>Status</th>
                                            <th class="text-center" style="width: 12%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($vehicle->documents as $doc)
                                            <tr>
                                                <td>{{ strtoupper($doc->document_type) }}</td>
                                                <td>{{ $doc->document_name }}</td>
                                                <td>{{ $doc->document_number ?: '—' }}</td>
                                                <td>{{ optional($doc->issue_date)->format('Y-m-d') ?: '—' }}</td>
                                                <td>
                                                    @if ($doc->expiry_date)
                                                        {{ $doc->expiry_date->format('d F Y') }}
                                                        @php $d = $doc->days_remaining; @endphp
                                                        @if ($d !== null)
                                                            <br>
                                                            <i class="fas fa-circle {{ $d < 0 ? 'text-danger' : 'text-success' }}"
                                                                style="font-size:0.65rem"></i>
                                                            <span class="{{ $d < 0 ? 'text-danger' : 'text-success' }}">{{ $d }}</span>
                                                        @endif
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $doc->statusBadgeClass() }}">
                                                        {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $canEditDoc = auth()->user()->can('vehicle-documents.edit');
                                                        $canDownloadDoc = auth()->user()->can('vehicle-documents.show') && $doc->file_path;
                                                        $canDeleteDoc = auth()->user()->can('vehicle-documents.delete');
                                                        $hasDocDropdown = $canDownloadDoc || $canDeleteDoc;
                                                    @endphp
                                                    @if ($canEditDoc || $hasDocDropdown)
                                                        <div class="btn-group btn-group-sm">
                                                            @if ($canEditDoc)
                                                                <button type="button" class="btn btn-primary"
                                                                    data-toggle="modal"
                                                                    data-target="#modal-edit-document-{{ $doc->id }}">
                                                                    Edit
                                                                </button>
                                                            @endif
                                                            @if ($hasDocDropdown)
                                                                <button type="button"
                                                                    class="btn btn-primary dropdown-toggle {{ $canEditDoc ? 'dropdown-toggle-split' : '' }}"
                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    @unless ($canEditDoc)
                                                                        Actions
                                                                    @endunless
                                                                    <span class="sr-only">Toggle Dropdown</span>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    @if ($canDownloadDoc)
                                                                        <a class="dropdown-item"
                                                                            href="{{ route('vehicles.documents.download', [$vehicle, $doc]) }}">
                                                                            <i class="fas fa-download mr-1"></i> Download
                                                                        </a>
                                                                    @endif
                                                                    @if ($canDownloadDoc && $canDeleteDoc)
                                                                        <div class="dropdown-divider"></div>
                                                                    @endif
                                                                    @if ($canDeleteDoc)
                                                                        <form action="{{ route('vehicles.documents.destroy', [$vehicle, $doc]) }}"
                                                                            method="POST" class="confirm-submit"
                                                                            data-confirm-message="Are you sure you want to delete this document?"
                                                                            data-confirm-yes="Yes, delete"
                                                                            data-confirm-icon="warning">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="dropdown-item text-danger">
                                                                                <i class="fas fa-trash mr-1"></i> Delete
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">No documents yet</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="travel-card">
                        <div class="card-head d-flex align-items-center justify-content-between">
                            <h2 class="mb-0"><i class="fas fa-gas-pump"></i> Fuel History (latest 20)</h2>
                            @can('fuel-records.show')
                                <a href="{{ route('fuel-records.index') }}" class="btn btn-outline-success btn-sm">
                                    View all
                                </a>
                            @endcan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Odometer</th>
                                            <th>Fuel Type</th>
                                            <th>Qty (L)</th>
                                            <th>Total</th>
                                            <th>Station</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($vehicle->fuelRecords as $fuel)
                                            <tr>
                                                <td>{{ optional($fuel->fuel_date)->format('Y-m-d') }}</td>
                                                <td>{{ number_format((int) $fuel->odometer) }}</td>
                                                <td>{{ $fuel->fuel_type }}</td>
                                                <td>{{ number_format((float) $fuel->quantity, 2) }}</td>
                                                <td>{{ number_format((float) $fuel->total_cost, 0, ',', '.') }}</td>
                                                <td>{{ $fuel->fuel_station ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No fuel records yet</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="col-lg-4">
                    <div class="travel-card">
                        <div class="card-head">
                            <h2><i class="fas fa-calendar-check"></i> Validity Period</h2>
                        </div>
                        <div class="card-body">
                            @foreach ($validityTypes as $type => $label)
                                @php
                                    $days = $vehicle->daysRemainingFor($type);
                                    $expiry = $vehicle->documentExpiry($type);
                                @endphp
                                <div class="vehicle-validity-item">
                                    <div class="info-label">{{ $label }}</div>
                                    <div class="info-value">
                                        {!! \App\Models\Vehicle::formatExpiryCell($expiry, $days) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="travel-action-buttons">
                        <a href="{{ route('vehicles.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i>
                            Back to List
                        </a>

                        @can('vehicles.edit')
                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endcan

                        @can('fuel-records.create')
                            <a href="{{ route('fuel-records.create', ['vehicle_id' => $vehicle->id]) }}"
                                class="btn-action vehicle-fuel-btn">
                                <i class="fas fa-gas-pump"></i> Add Fuel Record
                            </a>
                        @endcan

                        @can('vehicles.delete')
                            <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                                class="confirm-submit"
                                data-confirm-message="Are you sure you want to delete vehicle {{ $vehicle->kode }}?"
                                data-confirm-yes="Yes, delete"
                                data-confirm-icon="warning">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action vehicle-delete-btn w-100">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('vehicle-documents.edit')
        @foreach ($vehicle->documents as $doc)
            <div class="modal fade" id="modal-edit-document-{{ $doc->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('vehicles.documents.update', [$vehicle, $doc]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Document</h4>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="document_type" class="form-control" required>
                                        @foreach (\App\Models\VehicleDocument::TYPES as $type)
                                            <option value="{{ $type }}" @selected($doc->document_type === $type)>
                                                {{ strtoupper($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Document Name <span class="text-danger">*</span></label>
                                    <input type="text" name="document_name" class="form-control" required
                                        value="{{ $doc->document_name }}">
                                </div>
                                <div class="form-group">
                                    <label>Document No.</label>
                                    <input type="text" name="document_number" class="form-control"
                                        value="{{ $doc->document_number }}">
                                </div>
                                <div class="form-group">
                                    <label>Issue Date</label>
                                    <input type="date" name="issue_date" class="form-control"
                                        value="{{ optional($doc->issue_date)->format('Y-m-d') }}">
                                </div>
                                <div class="form-group">
                                    <label>Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control"
                                        value="{{ optional($doc->expiry_date)->format('Y-m-d') }}">
                                </div>
                                <div class="form-group">
                                    <label>Issuing Authority</label>
                                    <input type="text" name="issuing_authority" class="form-control"
                                        value="{{ $doc->issuing_authority }}">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        @foreach (['active', 'expired', 'pending_renewal', 'archived'] as $status)
                                            <option value="{{ $status }}" @selected($doc->status === $status)>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ $doc->notes }}</textarea>
                                </div>
                                <div class="form-group mb-0">
                                    <label>File {{ $doc->file_path ? '(replace)' : '' }}</label>
                                    @if ($doc->file_name)
                                        <p class="small text-muted mb-1">
                                            Current file: <strong>{{ $doc->file_name }}</strong>
                                        </p>
                                    @endif
                                    <input type="file" name="file" class="form-control-file"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="alert alert-warning mt-2 mb-0 py-2 small">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Uploading a new file will <strong>replace and delete</strong> the previous file.
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endcan

    @can('vehicle-documents.create')
        <div class="modal fade" id="modal-add-document" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('vehicles.documents.store', $vehicle) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Add Document</h4>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select name="document_type" class="form-control" required>
                                    @foreach (\App\Models\VehicleDocument::TYPES as $type)
                                        <option value="{{ $type }}">{{ strtoupper($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Document Name <span class="text-danger">*</span></label>
                                <input type="text" name="document_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Document No.</label>
                                <input type="text" name="document_number" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Issue Date</label>
                                <input type="date" name="issue_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Issuing Authority</label>
                                <input type="text" name="issuing_authority" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>File</label>
                                <input type="file" name="file" class="form-control-file"
                                    accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection

@section('scripts')
    <script>
        $(function() {
            $(document).on('submit', 'form.confirm-submit', function(e) {
                const form = this;
                if (form.dataset.submitting === 'true') {
                    return;
                }
                e.preventDefault();

                const message = form.getAttribute('data-confirm-message') || 'Continue with this action?';
                const title = form.getAttribute('data-confirm-title') || 'Confirm';
                const confirmText = form.getAttribute('data-confirm-yes') || 'Yes';
                const cancelText = form.getAttribute('data-confirm-no') || 'Cancel';
                const icon = form.getAttribute('data-confirm-icon') || 'warning';

                const proceed = () => {
                    form.dataset.submitting = 'true';
                    if (typeof toast_info === 'function') {
                        toast_info('Processing...');
                    }
                    form.submit();
                };

                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: confirmText,
                        cancelButtonText: cancelText,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            proceed();
                        }
                    });
                } else if (confirm(message)) {
                    proceed();
                }
            });
        });
    </script>
@endsection
