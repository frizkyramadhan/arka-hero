@extends('layouts.main')

@section('title', $title)

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    #modal-receipt .modal-body {
        background: #1a1a1a;
    }

    #modal-receipt-img {
        max-height: 80vh;
        width: auto;
        max-width: 100%;
    }

    .fuel-create-wrap {
        max-width: 880px;
    }

    .fuel-steps {
        display: flex;
        gap: 8px;
        margin-bottom: 1rem;
    }

    .fuel-step {
        flex: 1;
        text-align: center;
        padding: 10px 8px;
        border-radius: 10px;
        background: #f4f6f9;
        color: #6c757d;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid transparent;
        transition: all .2s ease;
    }

    .fuel-step.active {
        background: #e8f1ff;
        color: #007bff;
        border-color: #b8d4ff;
    }

    .fuel-step.done {
        background: #e8f8ef;
        color: #28a745;
        border-color: #b7e4c7;
    }

    .fuel-step .fuel-step-num {
        display: inline-flex;
        width: 22px;
        height: 22px;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: currentColor;
        color: #fff;
        font-size: 0.7rem;
        margin-right: 6px;
        vertical-align: middle;
    }

    .fuel-step.done .fuel-step-num,
    .fuel-step.active .fuel-step-num {
        background: currentColor;
        color: #fff;
    }

    .fuel-scan-btn {
        min-height: 180px;
        border: 2px dashed #adb5bd;
        border-radius: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
        transition: border-color .15s, background .15s, transform .15s;
        user-select: none;
    }

    .fuel-scan-btn:hover,
    .fuel-scan-btn.is-dragover {
        background: #eef5ff;
        border-color: #007bff;
        transform: translateY(-1px);
    }

    .fuel-scan-btn i {
        font-size: 2.75rem;
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

    .fuel-receipt-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        background: #111;
        border: 1px solid #dee2e6;
    }

    .fuel-receipt-card a {
        display: block;
        cursor: zoom-in;
    }

    .fuel-receipt-card img {
        display: block;
        width: 100%;
        max-height: 320px;
        object-fit: contain;
        background: #1a1a1a;
    }

    .fuel-receipt-card .fuel-zoom-hint {
        position: absolute;
        left: 10px;
        bottom: 10px;
        background: rgba(0, 0, 0, .65);
        color: #fff;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
        pointer-events: none;
    }

    @media (min-width: 768px) {
        .fuel-confirm-layout {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .fuel-confirm-receipt {
            flex: 0 0 240px;
            position: sticky;
            top: 1rem;
        }

        .fuel-confirm-fields {
            flex: 1;
            min-width: 0;
        }

        .fuel-receipt-card img {
            max-height: 420px;
        }
    }

    .fuel-scan-progress {
        display: none;
        text-align: center;
        padding: 1rem 0.5rem;
    }

    .fuel-scan-progress.is-on {
        display: block;
    }

    .fuel-scan-progress .fuel-scan-thumb {
        max-height: 160px;
        width: auto;
        max-width: 100%;
        border-radius: 10px;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
    }

    .fuel-progress-bar {
        height: 6px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
        margin: 0.75rem auto;
        max-width: 260px;
    }

    .fuel-progress-bar > span {
        display: block;
        height: 100%;
        width: 40%;
        background: linear-gradient(90deg, #007bff, #17a2b8);
        border-radius: 4px;
        animation: fuelScanSlide 1.2s ease-in-out infinite;
    }

    @keyframes fuelScanSlide {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(280%); }
    }

    .fuel-ai-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        background: #e8f8ef;
        color: #1e7e34;
        border-radius: 20px;
        padding: 4px 10px;
    }

    .fuel-manual-preview {
        display: none;
        margin-top: 0.75rem;
    }

    .fuel-manual-preview.is-on {
        display: block;
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
    <div class="container-fluid fuel-create-wrap">
        @if (!$aiEnabled)
        <div class="alert alert-warning">
            AI scan is not configured. Use <strong>Enter manually</strong>.
        </div>
        @endif

        <div class="fuel-steps" id="fuel-steps" aria-label="Progress">
            <div class="fuel-step active" data-step="scan" id="step-scan">
                <span class="fuel-step-num">1</span> Upload nota
            </div>
            <div class="fuel-step" data-step="confirm" id="step-confirm">
                <span class="fuel-step-num">2</span> Confirm &amp; submit
            </div>
        </div>

        <div id="scan-panel" class="card card-primary card-outline">
            <div class="card-body">
                <label class="fuel-scan-btn w-100 mb-3" for="scan-input" id="scan-label">
                    <i class="fas fa-camera mb-2"></i>
                    <strong>Scan / upload receipt</strong>
                    <small class="text-muted mt-1 px-3 text-center">
                        Foto nota SPBU, atau drag &amp; drop di sini
                    </small>
                </label>
                <input type="file" id="scan-input" accept="image/*" capture="environment" class="d-none">

                <div id="scan-loading" class="fuel-scan-progress">
                    <img id="scan-thumb" class="fuel-scan-thumb d-none" alt="Uploading receipt">
                    <div class="fuel-progress-bar"><span></span></div>
                    <p class="mb-1 font-weight-bold text-primary">
                        <i class="fas fa-magic"></i> Reading receipt with AI…
                    </p>
                    <small class="text-muted">Usually takes 10–30 seconds. Keep this page open.</small>
                </div>

                <button type="button" class="btn btn-outline-secondary btn-block" id="btn-manual">
                    <i class="fas fa-keyboard"></i> Enter manually
                </button>
            </div>
        </div>

        <form id="fuel-form" action="{{ route('fuel-records.my-requests.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="receipt_path" id="receipt_path" value="{{ old('receipt_path') }}">
            <input type="hidden" name="ai_raw_json" id="ai_raw_json" value="">
            <input type="hidden" name="ai_model" id="ai_model" value="">

            <div id="confirm-panel" class="card card-success card-outline">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <h3 class="card-title mb-0">Confirm details</h3>
                    <span class="fuel-ai-badge" id="ai-filled-badge">
                        <i class="fas fa-check-circle"></i> Filled from AI — please review
                    </span>
                </div>
                <div class="card-body">
                    <div class="fuel-confirm-layout">
                        <div class="fuel-confirm-receipt mb-3 mb-md-0">
                            <div class="fuel-receipt-card d-none" id="receipt-preview-wrap">
                                <a href="#modal-receipt" id="receipt-preview-link" data-toggle="modal" data-target="#modal-receipt" title="Enlarge receipt">
                                    <img id="receipt-preview" src="" alt="Receipt preview">
                                </a>
                                <span class="fuel-zoom-hint"><i class="fas fa-search-plus"></i> Tap to enlarge</span>
                            </div>
                            <small class="text-muted d-block mt-2 text-center d-none" id="receipt-preview-caption">
                                Compare fields with the nota
                            </small>
                        </div>
                        <div class="fuel-confirm-fields">
                            @include('fuel-records._my-form-fields', ['vehicles' => $vehicles])
                        </div>
                    </div>
                </div>
                <div class="card-footer fuel-sticky-actions">
                    <button type="submit" class="btn btn-success btn-lg btn-block mb-2">
                        <i class="fas fa-paper-plane"></i> Submit for verification
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-block" id="btn-back-scan">
                        <i class="fas fa-arrow-left"></i> Rescan / upload again
                    </button>
                </div>
            </div>

            <div id="manual-panel" class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Manual entry</h3>
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <label>Receipt photo <span class="text-danger">*</span></label>
                        <input type="file" name="receipt_image" id="manual-receipt" class="form-control-file" accept="image/*" capture="environment">
                    </div>
                    <div class="fuel-manual-preview" id="manual-preview-wrap">
                        <div class="fuel-receipt-card">
                            <a href="#modal-receipt" id="manual-preview-link" data-toggle="modal" data-target="#modal-receipt" title="Enlarge receipt">
                                <img id="manual-preview" src="" alt="Receipt preview">
                            </a>
                            <span class="fuel-zoom-hint"><i class="fas fa-search-plus"></i> Tap to enlarge</span>
                        </div>
                    </div>
                    @include('fuel-records._my-form-fields', ['vehicles' => $vehicles, 'prefix' => 'manual'])
                </div>
                <div class="card-footer fuel-sticky-actions">
                    <button type="submit" class="btn btn-success btn-lg btn-block mb-2" id="btn-manual-submit">
                        <i class="fas fa-paper-plane"></i> Submit for verification
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-block" id="btn-back-scan-2">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
            </div>
        </form>

        {{-- AdminLTE / Bootstrap modal (not ekko-lightbox: temp receipt URL has no extension) --}}
        <div class="modal fade" id="modal-receipt" tabindex="-1" role="dialog" aria-labelledby="modal-receipt-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal-receipt-title">Receipt</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center p-2">
                        <img id="modal-receipt-img" class="img-fluid" src="" alt="Receipt enlarged">
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
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
$fuelOldReceiptUrl = filled($fuelOldReceiptPath)
    ? route('fuel-records.my-requests.receipt-temp', ['path' => encrypt($fuelOldReceiptPath)])
    : null;
@endphp
<script>
    (function() {
        var aiEnabled = @json($aiEnabled);
        var parseUrl = @json(route('fuel-records.my-requests.parse-receipt'));
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var restorePanel = @json((bool) $fuelRestorePanel);
        var restoreConfirm = @json((bool) $fuelRestoreConfirm);
        var oldReceiptPath = @json($fuelOldReceiptPath);
        var oldReceiptUrl = @json($fuelOldReceiptUrl);
        var localObjectUrl = null;

        function revokeLocalUrl() {
            if (localObjectUrl) {
                URL.revokeObjectURL(localObjectUrl);
                localObjectUrl = null;
            }
        }

        function setSteps(name) {
            var $scan = $('#step-scan');
            var $confirm = $('#step-confirm');
            $scan.removeClass('active done');
            $confirm.removeClass('active done');
            if (name === 'scan') {
                $scan.addClass('active');
                return;
            }
            // confirm + manual both are step 2
            $scan.addClass('done');
            $confirm.addClass('active');
        }

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
            setSteps(name);
            if (name === 'scan') {
                $('#scan-loading').removeClass('is-on');
                $('#scan-label').show();
            }
        }

        function setReceiptPreview(url) {
            if (!url) {
                $('#receipt-preview-wrap, #receipt-preview-caption').addClass('d-none');
                return;
            }
            $('#receipt-preview').attr('src', url);
            $('#receipt-preview-wrap, #receipt-preview-caption').removeClass('d-none');
        }

        function setManualPreview(url) {
            if (!url) {
                $('#manual-preview-wrap').removeClass('is-on');
                return;
            }
            $('#manual-preview').attr('src', url);
            $('#manual-preview-wrap').addClass('is-on');
        }

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        $('#modal-receipt').on('show.bs.modal', function(e) {
            var $trigger = $(e.relatedTarget);
            var src = $trigger.find('img').attr('src') || $trigger.data('src') || '';
            $('#modal-receipt-img').attr('src', src);
        });
        $('#modal-receipt').on('hidden.bs.modal', function() {
            $('#modal-receipt-img').attr('src', '');
        });

        $('#btn-manual').on('click', function() {
            showPanel('manual');
            $('#receipt_path').val('');
            $('#ai-filled-badge').hide();
        });
        $('#btn-back-scan, #btn-back-scan-2').on('click', function() {
            revokeLocalUrl();
            $('#scan-input').val('');
            $('#manual-receipt').val('');
            setManualPreview(null);
            setReceiptPreview(null);
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
            // Total follows the nota only — never auto-calc from qty × price.
            if (data.total_cost != null) {
                $('#total_cost').val(data.total_cost);
            }
            if (data.fuel_station) $('#fuel_station').val(data.fuel_station);
            if (data.receipt_number) $('#receipt_number').val(data.receipt_number);
            if (data.notes) $('#notes').val(data.notes);
        }

        // Do not bind qty/price → total. SPBU totals are often rounded on the receipt.
        // Drag & drop on scan zone
        var $label = $('#scan-label');
        $label.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('is-dragover');
        });
        $label.on('dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('is-dragover');
        });
        $label.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
            if (!files || !files.length) return;
            var input = document.getElementById('scan-input');
            var dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
            $(input).trigger('change');
        });

        function startScanUi(file) {
            revokeLocalUrl();
            localObjectUrl = URL.createObjectURL(file);
            $('#scan-thumb').attr('src', localObjectUrl).removeClass('d-none');
            $('#scan-label').hide();
            $('#scan-loading').addClass('is-on');
        }

        $('#scan-input').on('change', function() {
            var file = this.files && this.files[0];
            if (!file) return;
            if (!aiEnabled) {
                showPanel('manual');
                var dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('manual-receipt').files = dt.files;
                revokeLocalUrl();
                localObjectUrl = URL.createObjectURL(file);
                setManualPreview(localObjectUrl);
                return;
            }

            startScanUi(file);

            var fd = new FormData();
            fd.append('receipt_image', file);
            fd.append('_token', csrf);

            $.ajax({
                url: parseUrl,
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#scan-loading').removeClass('is-on');
                    $('#scan-label').show();
                    if (!res.success) {
                        toastrError(res.message || 'Parse failed');
                        showPanel('manual');
                        var dtFail = new DataTransfer();
                        dtFail.items.add(file);
                        document.getElementById('manual-receipt').files = dtFail.files;
                        setManualPreview(localObjectUrl);
                        return;
                    }
                    var d = res.data;
                    $('#receipt_path').val(d.receipt_path || '');
                    $('#ai_raw_json').val(JSON.stringify(d.ai_raw_json || d));
                    $('#ai_model').val(d.ai_model || '');
                    setReceiptPreview(d.receipt_url || localObjectUrl);
                    $('#ai-filled-badge').show();
                    fillConfirm(d);
                    showPanel('confirm');
                },
                error: function(xhr) {
                    $('#scan-loading').removeClass('is-on');
                    $('#scan-label').show();
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'AI parse failed. Use manual entry.';
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                    else alert(msg);
                    var path = xhr.responseJSON && xhr.responseJSON.receipt_path;
                    if (path) $('#receipt_path').val(path);
                    showPanel('manual');
                    var dtErr = new DataTransfer();
                    dtErr.items.add(file);
                    document.getElementById('manual-receipt').files = dtErr.files;
                    setManualPreview(localObjectUrl);
                }
            });
        });

        $('#manual-receipt').on('change', function() {
            var file = this.files && this.files[0];
            if (!file) {
                setManualPreview(null);
                return;
            }
            revokeLocalUrl();
            localObjectUrl = URL.createObjectURL(file);
            setManualPreview(localObjectUrl);
        });

        function toastrError(msg) {
            if (typeof toastr !== 'undefined') toastr.error(msg);
            else alert(msg);
        }

        showPanel('scan');
        if (restorePanel) {
            if (restoreConfirm) {
                showPanel('confirm');
                if (oldReceiptPath) {
                    $('#receipt_path').val(oldReceiptPath);
                }
                if (oldReceiptUrl) {
                    setReceiptPreview(oldReceiptUrl);
                }
                $('#ai-filled-badge').hide();
            } else {
                showPanel('manual');
            }
        }
    })();
</script>
@endsection
