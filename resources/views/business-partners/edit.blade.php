@extends('layouts.main')

@section('title', 'Edit Business Partner')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Business Partner</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('business-partners.index') }}">Business Partners</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('business-partners.update', $businessPartner->id) }}" id="businessPartnerForm">
                @csrf
                @method('PUT')
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <!-- Main Business Partner Info Card -->
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-building mr-2"></i>
                                    <strong>Business Partner Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- BP Code & BP Name -->
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="bp_code">
                                                <i class="fas fa-hashtag mr-1"></i>
                                                BP Code <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                </div>
                                                <input type="text" name="bp_code" id="bp_code" 
                                                    class="form-control @error('bp_code') is-invalid @enderror" 
                                                    value="{{ old('bp_code', $businessPartner->bp_code) }}" 
                                                    placeholder="Enter Business Partner Code" required>
                                                @error('bp_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Unique code identifier for this business partner
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <!-- BP Name -->
                                        <div class="form-group">
                                            <label for="bp_name">
                                                <i class="fas fa-building mr-1"></i>
                                                BP Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                                </div>
                                                <input type="text" name="bp_name" id="bp_name" 
                                                    class="form-control @error('bp_name') is-invalid @enderror" 
                                                    value="{{ old('bp_name', $businessPartner->bp_name) }}" 
                                                    placeholder="Enter Business Partner Name" required>
                                                @error('bp_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Full name of the business partner/vendor
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- BP Address -->
                                <div class="form-group">
                                    <label for="bp_address">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        BP Address
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <textarea name="bp_address" id="bp_address" 
                                            class="form-control @error('bp_address') is-invalid @enderror" 
                                            rows="3" 
                                            placeholder="Enter complete address">{{ old('bp_address', $businessPartner->bp_address) }}</textarea>
                                        @error('bp_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Complete address including street, city, and postal code
                                    </small>
                                </div>

                                <!-- BP Phone & Status -->
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="bp_phone">
                                                <i class="fas fa-phone mr-1"></i>
                                                BP Phone
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <input type="text" name="bp_phone" id="bp_phone" 
                                                    class="form-control @error('bp_phone') is-invalid @enderror" 
                                                    value="{{ old('bp_phone', $businessPartner->bp_phone) }}" 
                                                    placeholder="e.g., +62 21 1234 5678">
                                                @error('bp_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Contact phone number with country code
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="status">
                                                <i class="fas fa-toggle-on mr-1"></i>
                                                Status <span class="text-danger">*</span>
                                            </label>
                                            <select name="status" id="status" 
                                                class="form-control select2bs4 @error('status') is-invalid @enderror" 
                                                required>
                                                <option value="">Select Status</option>
                                                <option value="active" {{ old('status', $businessPartner->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $businessPartner->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                Active partners can be used in flight issuances
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="form-group">
                                    <label for="notes">
                                        <i class="fas fa-sticky-note mr-1"></i>
                                        Notes
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-sticky-note"></i></span>
                                        </div>
                                        <textarea name="notes" id="notes" 
                                            class="form-control @error('notes') is-invalid @enderror" 
                                            rows="2" 
                                            placeholder="Additional notes or remarks">{{ old('notes', $businessPartner->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Optional notes or additional information about this business partner
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Business Partner
                                </button>
                                <a href="{{ route('business-partners.index') }}" class="btn btn-default">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-4">
                        <!-- Information Card -->
                        <div class="card card-info card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="callout callout-info">
                                    <h5><i class="icon fas fa-lightbulb"></i> Tips</h5>
                                    <p>
                                        <strong>BP Code:</strong> Use a unique, short code for easy identification (e.g., "BP001", "GARUDA", "CITILINK").
                                    </p>
                                    <p>
                                        <strong>Status:</strong> Only active business partners will be available for selection when creating flight issuances.
                                    </p>
                                    <p>
                                        <strong>Contact Info:</strong> Ensure phone number and address are accurate for communication purposes.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Auto-uppercase BP Code
            $('#bp_code').on('input', function() {
                $(this).val($(this).val().toUpperCase());
            });

            // Form validation feedback
            $('#businessPartnerForm').on('submit', function(e) {
                let isValid = true;
                
                // Check required fields
                $('#businessPartnerForm [required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fill in all required fields',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        });
    </script>
@endsection
