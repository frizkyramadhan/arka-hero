@php
    $addEntitlementContext = $addEntitlementContext ?? [
        'can_add_annual' => false,
        'can_add_lsl' => false,
    ];
    $showAnnual = $addEntitlementContext['can_add_annual'] ?? false;
    $showLsl = $addEntitlementContext['can_add_lsl'] ?? false;
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-end">
    @if ($showAnnual)
        <a href="{{ route('leave.entitlements.employee.edit', ['employee' => $employee->id, 'scope' => 'annual']) }}"
            class="btn btn-info mr-1 mb-1" title="Tambah hak cuti periode 1 tahun">
            <i class="fas fa-plus mr-1"></i>Periode Tahunan
        </a>
    @endif

    @if ($showLsl)
        <a href="{{ route('leave.entitlements.employee.edit', ['employee' => $employee->id, 'scope' => 'lsl']) }}"
            class="btn btn-warning mb-1" title="Tambah hak cuti panjang periode 5–6 tahun">
            <i class="fas fa-plus mr-1"></i>Periode Cuti Panjang
        </a>
    @endif

    @if (!$showAnnual && !$showLsl)
        <small class="text-muted mb-1">
            <i class="fas fa-check-circle text-success"></i> Periode aktif — edit lewat sidebar
        </small>
    @endif
</div>
