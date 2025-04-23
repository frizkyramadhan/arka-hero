@php
    $loadingOverlay = '<div class="overlay-wrapper">
        <div class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <div class="text-bold pt-2">Loading...</div>
        </div>
    </div>';
@endphp

<div id="bank-pane" class="content" role="tabpanel" aria-labelledby="bank-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Bank Account</h5>
        <div>
            @if ($bank == null)
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-bank">
                    <i class="fas fa-plus mr-1"></i> Add Bank
                </button>
            @else
                <button class="btn btn-primary" data-toggle="modal" data-target="#modal-bank-{{ $bank->id }}">
                    <i class="fas fa-pen-square mr-1"></i> Edit Bank
                </button>
                <form action="{{ url('employeebanks/' . $employee->id . '/' . $bank->id) }}" method="post"
                    onsubmit="return confirm('Are you sure want to delete this bank account data?')" class="d-inline">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger" title="Delete Bank Account Data">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($bank == null)
        <div class="text-center py-5">
            <img src="{{ asset('assets/dist/img/bank-empty.png') }}" alt="No Bank Data" class="img-fluid mb-3"
                style="max-height: 120px; opacity: 0.5;">
            <h6 class="text-muted">No bank account information available</h6>
            <p class="text-muted small">Click "Add Bank" button to register employee's bank
                account details</p>
        </div>
    @else
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-university mr-2"></i>
                            Bank Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-university"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Bank Name</span>
                                <span class="info-box-number">{{ $bank->banks->bank_name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-map-marker-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Branch</span>
                                <span class="info-box-number">{{ $bank->bank_account_branch ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user mr-2"></i>
                            Account Holder
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-hashtag"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Account Number</span>
                                <span class="info-box-number">{{ $bank->bank_account_no ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-primary"><i class="fas fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Account Name</span>
                                <span class="info-box-number">{{ $bank->bank_account_name ?? '-' }}</span>
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
        const bankPane = document.getElementById('bank-pane');
        const bankTrigger = document.getElementById('bank-pane-trigger');

        if (bankTrigger) {
            bankTrigger.addEventListener('click', function() {
                // Show loading overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay-wrapper';
                overlay.innerHTML = `{!! $loadingOverlay !!}`;
                bankPane.appendChild(overlay);

                // Simulate loading delay (remove this in production)
                setTimeout(() => {
                    // Remove loading overlay
                    bankPane.removeChild(overlay);
                    // Show content
                    bankPane.classList.add('loaded');
                }, 500);
            });
        }
    });
</script>
