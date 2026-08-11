@php
    $a = $assignment;
@endphp
<div class="travel-card travel-info-card">
    <div class="card-head">
        <h2><i class="fas fa-clipboard-list"></i> Assignment Information</h2>
    </div>
    <div class="card-body p-0">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon" style="background-color: #3498db;"><i class="fas fa-hashtag"></i></div>
                <div class="info-content">
                    <div class="info-label">FOA No.</div>
                    <div class="info-value">{{ $a->form_number }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #16a085;"><i class="fas fa-file-alt"></i></div>
                <div class="info-content">
                    <div class="info-label">Letter Number</div>
                    <div class="info-value">{{ $a->letter_number ?: '—' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #e67e22;"><i class="fas fa-calendar-day"></i></div>
                <div class="info-content">
                    <div class="info-label">Date</div>
                    <div class="info-value">{{ optional($a->assignment_date)->format('l, d F Y') }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #9b59b6;"><i class="fas fa-id-badge"></i></div>
                <div class="info-content">
                    <div class="info-label">Driver</div>
                    <div class="info-value">{{ $a->driver_name }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #1abc9c;"><i class="fas fa-car"></i></div>
                <div class="info-content">
                    <div class="info-label">Vehicle</div>
                    <div class="info-value">
                        @if ($a->vehicle)
                            <a href="{{ route('vehicles.show', $a->vehicle) }}">{{ $a->vehicle_kode }} — {{ $a->license_plate }}</a>
                        @else
                            {{ $a->vehicle_kode }} — {{ $a->license_plate }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #27ae60;"><i class="fas fa-map-marker-alt"></i></div>
                <div class="info-content">
                    <div class="info-label">Origin (lokasi awal)</div>
                    <div class="info-value">
                        {{ $a->origin_destination }}
                        @if ($a->origin_is_manual)
                            <span class="badge badge-secondary">Manual</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon" style="background-color: #7f8c8d;"><i class="fas fa-user-edit"></i></div>
                <div class="info-content">
                    <div class="info-label">Requestor</div>
                    <div class="info-value">{{ $a->requestor?->name ?? '—' }}</div>
                </div>
            </div>
        </div>
        @if ($a->remarks)
            <div class="px-3 pb-3">
                <div class="info-label">Remarks / Keterangan</div>
                <div class="info-value">{{ $a->remarks }}</div>
            </div>
        @endif
    </div>
</div>
