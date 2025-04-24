@php
    $loadingOverlay = '<div class="overlay-wrapper">
        <div class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <div class="text-bold pt-2">Loading...</div>
        </div>
    </div>';
@endphp

<div id="tax-pane" class="content" role="tabpanel" aria-labelledby="tax-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Tax Identification Number (NPWP)</h5>
        <div>
            @if ($tax == null)
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-tax">
                    <i class="fas fa-plus mr-1"></i> Add Tax Info
                </button>
            @else
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-tax-{{ $tax->id }}">
                    <i class="fas fa-pen-square mr-1"></i> Edit Tax Info
                </button>
                <form action="{{ url('taxidentifications/' . $employee->id . '/' . $tax->id) }}" method="post"
                    onsubmit="return confirm('Are you sure want to delete this tax identification data?')"
                    class="d-inline">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger" title="Delete Tax Identification Data">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($tax == null)
        <div class="empty-state">
            <i class="fas fa-exclamation-circle"></i>
            <h6>No Data Available</h6>
            <p>No tax identification information found for this employee</p>
        </div>
    @else
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            Tax Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">NPWP Number</span>
                                <span class="info-box-number">{{ $tax->tax_no ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Registration Date</span>
                                <span
                                    class="info-box-number">{{ $tax ? ($tax->tax_valid_date ? date('d M Y', strtotime($tax->tax_valid_date)) : '-') : '-' }}</span>
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
        min-height: 80px;
        margin-bottom: 1rem;
        padding: 0.5rem;
        border-radius: 0.25rem;
    }

    .info-box-icon {
        float: left;
        height: 70px;
        width: 70px;
        text-align: center;
        font-size: 1.5rem;
        line-height: 70px;
        border-radius: 0.25rem;
    }

    .info-box-content {
        padding: 5px 10px;
        margin-left: 10px;
    }

    .info-box-text {
        display: block;
        font-size: 0.8125rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-transform: uppercase;
        color: #6c757d;
    }

    .info-box-number {
        display: block;
        font-weight: 600;
        font-size: 0.9375rem;
        margin-top: 0.25rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const taxPane = document.getElementById('tax-pane');
        const taxTrigger = document.getElementById('tax-pane-trigger');

        if (taxTrigger) {
            taxTrigger.addEventListener('click', function() {
                // Show loading overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay-wrapper';
                overlay.innerHTML = `{!! $loadingOverlay !!}`;
                taxPane.appendChild(overlay);

                // Simulate loading delay (remove this in production)
                setTimeout(() => {
                    // Remove loading overlay
                    taxPane.removeChild(overlay);
                    // Show content
                    taxPane.classList.add('loaded');
                }, 500);
            });
        }
    });
</script>
