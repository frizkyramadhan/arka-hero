@php
    $loadingOverlay = '<div class="overlay-wrapper">
        <div class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <div class="text-bold pt-2">Loading...</div>
        </div>
    </div>';
@endphp

<div id="additional-pane" class="content" role="tabpanel" aria-labelledby="additional-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Additional Data</h5>
        <div>
            @if ($additional == null)
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-additional">
                    <i class="fas fa-plus mr-1"></i> Add Data
                </button>
            @else
                <button class="btn btn-primary" data-toggle="modal"
                    data-target="#modal-additional-{{ $additional->id }}">
                    <i class="fas fa-pen-square mr-1"></i> Edit Data
                </button>
                <form action="{{ url('additionaldatas/' . $employee->id . '/' . $additional->id) }}" method="post"
                    onsubmit="return confirm('Are you sure want to delete this additional data?')" class="d-inline">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger" title="Delete Additional Data">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($additional == null)
        <div class="text-center py-5">
            <img src="{{ asset('assets/dist/img/additional-empty.png') }}" alt="No Additional Data"
                class="img-fluid mb-3" style="max-height: 120px; opacity: 0.5;">
            <h6 class="text-muted">No additional information available</h6>
            <p class="text-muted small">Click "Add Data" button to register employee's additional details</p>
        </div>
    @else
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tshirt mr-2"></i>
                            Clothing Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-tshirt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Cloth Size</span>
                                        <span class="info-box-number">{{ $additional->cloth_size ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-socks"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Pants Size</span>
                                        <span class="info-box-number">{{ $additional->pants_size ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-shoe-prints"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Shoes Size</span>
                                        <span class="info-box-number">{{ $additional->shoes_size ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-glasses"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Glasses</span>
                                        <span class="info-box-number">{{ $additional->glasses ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-ruler-combined mr-2"></i>
                            Physical Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-ruler-vertical"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Height</span>
                                        <span class="info-box-number">{{ $additional->height ?? '-' }} cm</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-weight"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Weight</span>
                                        <span class="info-box-number">{{ $additional->weight ?? '-' }} kg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .overlay-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        padding-top: 2rem;
    }

    .overlay i {
        color: #007bff;
    }

    .content {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease-in-out;
        position: relative;
    }

    .content.loaded {
        opacity: 1;
        visibility: visible;
    }

    /* Info Box Styles */
    .info-box {
        min-height: 60px;
        margin-bottom: 0.75rem;
        padding: 0.375rem;
        display: flex;
        align-items: center;
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0, 0, 0, 0.1);
    }

    .info-box-icon {
        height: 50px;
        width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        border-radius: 0.25rem;
    }

    .info-box-content {
        padding: 3px 8px;
        margin-left: 60px;
    }

    .info-box-text {
        display: block;
        font-size: 0.75rem;
        color: #6c757d;
    }

    .info-box-number {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        margin-top: 0.125rem;
    }

    /* Card Styles */
    .card {
        margin-bottom: 1rem;
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        padding: 0.75rem 1rem;
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #495057;
    }

    .card-body {
        padding: 1rem;
    }

    /* Empty State Styles */
    .text-center {
        text-align: center;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .small {
        font-size: 0.875rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const additionalPane = document.getElementById('additional-pane');
        const additionalTrigger = document.getElementById('additional-pane-trigger');

        if (additionalTrigger) {
            additionalTrigger.addEventListener('click', function() {
                // Show loading overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay-wrapper';
                overlay.innerHTML = `{!! $loadingOverlay !!}`;
                additionalPane.appendChild(overlay);

                // Simulate loading delay (remove this in production)
                setTimeout(() => {
                    // Remove loading overlay
                    additionalPane.removeChild(overlay);
                    // Show content
                    additionalPane.classList.add('loaded');
                }, 500);
            });
        }
    });
</script>
