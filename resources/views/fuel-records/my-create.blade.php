@extends('layouts.main')

@section('title', $title)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .fuel-scan-btn {
        min-height: 140px;
        border: 2px dashed #6c757d;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: #f8f9fa;
    }

    .fuel-scan-btn:hover {
        background: #e9ecef;
        border-color: #007bff;
    }

    .fuel-scan-btn i {
        font-size: 2.5rem;
        color: #007bff;
    }

    #confirm-panel,
    #manual-panel {
        display: none;
    }

    .fuel-sticky-actions {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: 12px 0;
        border-top: 1px solid #dee2e6;
        z-index: 10;
    }

    #receipt-preview {
        max-height: 220px;
        object-fit: contain;
        width: 100%;
        border-radius: 8px;
    }
</style>
@endsection

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $subtitle }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('fuel-records.my-requests') }}">My Fuel Log</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid" style="max-width: 640px">
        @if (!$aiEnabled)
        <div class="alert alert-warning">
            AI scan is not configured. Use <strong>Enter manually</strong>.
        </div>
        @endif

        <div id="scan-panel" class="card card-primary card-outline">
            <div class="card-body">
                <label class="fuel-scan-btn w-100 mb-3" for="scan-input" id="scan-label">
                    <i class="fas fa-camera mb-2"></i>
                    <strong>Scan receipt</strong>
                    <small class="text-muted mt-1">Take a photo of the SPBU nota</small>
                </label>
                <input type="file" id="scan-input" accept="image/*" capture="environment" class="d-none">
                <div id="scan-loading" class="text-center d-none py-3">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 mb-0">Reading receipt with AI…</p>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-block" id="btn-manual">
                    Enter manually
                </button>
                <p class="text-muted small mt-3 mb-0" id="pwa-install-hint" style="display:none">
                    <i class="fas fa-mobile-alt"></i>
                    <a href="#" id="pwa-install-link">Install app</a> for faster access from your home screen.
                </p>
            </div>
        </div>

        <form id="fuel-form" action="{{ route('fuel-records.my-requests.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="receipt_path" id="receipt_path" value="{{ old('receipt_path') }}">
            <input type="hidden" name="ai_raw_json" id="ai_raw_json" value="">
            <input type="hidden" name="ai_model" id="ai_model" value="">

            <div id="confirm-panel" class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">Confirm details</h3>
                </div>
                <div class="card-body">
                    <img id="receipt-preview" src="" alt="Receipt" class="mb-3 d-none">
                    @include('fuel-records._my-form-fields', ['vehicles' => $vehicles])
                </div>
                <div class="card-footer fuel-sticky-actions">
                    <button type="submit" class="btn btn-success btn-lg btn-block mb-2">
                        <i class="fas fa-paper-plane"></i> Submit for verification
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" id="btn-back-scan">Back</button>
                </div>
            </div>

            <div id="manual-panel" class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Manual entry</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Receipt photo <span class="text-danger">*</span></label>
                        <input type="file" name="receipt_image" id="manual-receipt" class="form-control-file" accept="image/*" capture="environment">
                    </div>
                    @include('fuel-records._my-form-fields', ['vehicles' => $vehicles, 'prefix' => 'manual'])
                </div>
                <div class="card-footer fuel-sticky-actions">
                    <button type="submit" class="btn btn-success btn-lg btn-block mb-2" id="btn-manual-submit">
                        <i class="fas fa-paper-plane"></i> Submit for verification
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg btn-block" id="btn-back-scan-2">Back</button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
@php
// Do not use "$errors->any()": some Blade formatters break "->" into "- >".
$fuelRestorePanel = filled(old('vehicle_id')) || (isset($errors) && count($errors) > 0);
$fuelRestoreConfirm = filled(old('receipt_path'));
$fuelOldReceiptPath = old('receipt_path');
@endphp
<script>
    (function() {
        var aiEnabled = @json($aiEnabled);
        var parseUrl = @json(route('fuel-records.my-requests.parse-receipt'));
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var restorePanel = @json((bool) $fuelRestorePanel);
        var restoreConfirm = @json((bool) $fuelRestoreConfirm);
        var oldReceiptPath = @json($fuelOldReceiptPath);

        function showPanel(name) {
            $('#scan-panel').toggle(name === 'scan');
            $('#confirm-panel').toggle(name === 'confirm');
            $('#manual-panel').toggle(name === 'manual');
            $('#confirm-panel :input').prop('disabled', name !== 'confirm');
            $('#manual-panel :input').prop('disabled', name !== 'manual');
            if (name === 'manual') {
                $('#manual-panel :input').prop('disabled', false);
                $('#receipt_path, #ai_raw_json, #ai_model').prop('disabled', true);
            }
            if (name === 'confirm') {
                $('#confirm-panel :input').prop('disabled', false);
                $('#manual-receipt').prop('disabled', true);
            }
        }

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        $('#btn-manual').on('click', function() {
            showPanel('manual');
            $('#receipt_path').val('');
        });
        $('#btn-back-scan, #btn-back-scan-2').on('click', function() {
            showPanel('scan');
        });

        function fillConfirm(data) {
            if (data.vehicle_id) {
                $('#vehicle_id').val(data.vehicle_id).trigger('change');
            }
            if (data.fuel_date) $('#fuel_date').val(data.fuel_date);
            if (data.odometer != null) $('#odometer').val(data.odometer);
            if (data.fuel_type) $('#fuel_type').val(data.fuel_type);
            if (data.quantity != null) $('#quantity').val(data.quantity);
            if (data.price_per_liter != null) $('#price_per_liter').val(data.price_per_liter);
            if (data.total_cost != null) $('#total_cost').val(data.total_cost);
            if (data.fuel_station) $('#fuel_station').val(data.fuel_station);
            if (data.receipt_number) $('#receipt_number').val(data.receipt_number);
            if (data.notes) $('#notes').val(data.notes);
            recalc();
        }

        function recalc() {
            var q = parseFloat($('#quantity').val()) || 0;
            var p = parseFloat($('#price_per_liter').val()) || 0;
            if (!$('#total_cost').data('locked')) {
                $('#total_cost').val(q && p ? (q * p).toFixed(2) : '');
            }
        }
        $(document).on('input', '#quantity, #price_per_liter', recalc);

        $('#scan-input').on('change', function() {
            var file = this.files && this.files[0];
            if (!file) return;
            if (!aiEnabled) {
                showPanel('manual');
                var dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('manual-receipt').files = dt.files;
                return;
            }

            var fd = new FormData();
            fd.append('receipt_image', file);
            fd.append('_token', csrf);
            $('#scan-loading').removeClass('d-none');
            $('#scan-label').addClass('d-none');

            $.ajax({
                url: parseUrl,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#scan-loading').addClass('d-none');
                    $('#scan-label').removeClass('d-none');
                    if (!res.success) {
                        toastrError(res.message || 'Parse failed');
                        showPanel('manual');
                        return;
                    }
                    var d = res.data;
                    $('#receipt_path').val(d.receipt_path || '');
                    $('#ai_raw_json').val(JSON.stringify(d.ai_raw_json || d));
                    $('#ai_model').val(d.ai_model || '');
                    if (d.receipt_url) {
                        $('#receipt-preview').attr('src', d.receipt_url).removeClass('d-none');
                    }
                    fillConfirm(d);
                    showPanel('confirm');
                },
                error: function(xhr) {
                    $('#scan-loading').addClass('d-none');
                    $('#scan-label').removeClass('d-none');
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'AI parse failed. Use manual entry.';
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                    else alert(msg);
                    var path = xhr.responseJSON && xhr.responseJSON.receipt_path;
                    if (path) $('#receipt_path').val(path);
                    showPanel('manual');
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    document.getElementById('manual-receipt').files = dt.files;
                }
            });
        });

        function toastrError(msg) {
            if (typeof toastr !== 'undefined') toastr.error(msg);
            else alert(msg);
        }

        var deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            $('#pwa-install-hint').show();
        });
        $('#pwa-install-link').on('click', function(e) {
            e.preventDefault();
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt = null;
            $('#pwa-install-hint').hide();
        });

        showPanel('scan');
        if (restorePanel) {
            if (restoreConfirm) {
                showPanel('confirm');
                if (oldReceiptPath) {
                    $('#receipt_path').val(oldReceiptPath);
                }
            } else {
                showPanel('manual');
            }
        }
    })();
</script>
@endsection