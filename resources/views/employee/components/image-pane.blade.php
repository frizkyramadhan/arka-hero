@php
    $loadingOverlay = '<div class="overlay-wrapper">
        <div class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <div class="text-bold pt-2">Loading...</div>
        </div>
    </div>';
@endphp

<div id="image-pane" class="content" role="tabpanel" aria-labelledby="image-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Employee Images</h5>
        <div>
            @if ($images->isNotEmpty())
                <a href="{{ url('employees/deleteImages/' . $employee->id) }}" class="btn btn-danger"
                    onclick="return confirm('Are you sure you want to delete all images?');">
                    <i class="fas fa-trash mr-1"></i> Delete All Images
                </a>
            @endif
        </div>
    </div>

    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle mr-2"></i>
        Upload employee images including ID cards, profile photos, and other relevant documents.
    </div>

    <form action="{{ url('employees/addImages/' . $employee->id) }}" method="POST" enctype="multipart/form-data"
        class="mb-4">
        @csrf
        <div class="form-group">
            <label for="images_upload" class="form-label">Upload Images</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="images_upload" name="filename[]" multiple required>
                <label class="custom-file-label" for="images_upload">Choose files...</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload mr-1"></i> Upload
        </button>
    </form>

    <div class="card bg-light mb-3">
        <div class="card-body">
            <h6 class="card-title">
                <i class="fas fa-lightbulb text-warning mr-2"></i>Image Guidelines
            </h6>
            <br>
            <ul class="mb-0 pl-3">
                <li>Supported formats: JPG, PNG. Maximum file size: 2MB.</li>
                <li>Profile photos should be clear and professional</li>
                <li>ID card images must be legible</li>
                <li>All uploads must be appropriate for workplace use</li>
            </ul>
        </div>
    </div>

    <div class="row mt-4">
        @forelse ($images as $image)
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    <a href="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                        data-toggle="lightbox" data-title="{{ $image->filename }}" data-gallery="gallery">
                        <img src="{{ asset('images/' . $image->employee_id . '/' . $image->filename) }}"
                            class="card-img-top" alt="{{ $image->filename }}"
                            style="height: 200px; object-fit: cover;" />
                    </a>
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            @if ($image->is_profile == 0)
                                <a href="{{ url('employees/setProfile/' . $employee->id . '/' . $image->id) }}"
                                    class="btn btn-primary btn-sm" title="Set Profile Picture">
                                    <i class="fas fa-id-badge mr-1"></i> Set Profile
                                </a>
                            @else
                                <span class="badge badge-success">
                                    <i class="fas fa-check mr-1"></i> Profile Picture
                                </span>
                            @endif
                            <a href="{{ url('employees/deleteImage/' . $employee->id . '/' . $image->id) }}"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this image?');"
                                title="Delete Image">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    No images available. Please upload some images.
                </div>
            </div>
        @endforelse
    </div>
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

    /* Image Grid Styles */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }

    .col-sm-6,
    .col-md-4,
    .col-lg-3 {
        position: relative;
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
    }

    @media (min-width: 576px) {
        .col-sm-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (min-width: 768px) {
        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
    }

    @media (min-width: 992px) {
        .col-lg-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }
    }

    /* Image Card Styles */
    .card-img-top {
        width: 100%;
        border-top-left-radius: calc(0.25rem - 1px);
        border-top-right-radius: calc(0.25rem - 1px);
    }

    .card-body {
        flex: 1 1 auto;
        padding: 1.25rem;
    }

    /* Button Styles */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    .btn-primary {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-danger {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .badge-success {
        color: #fff;
        background-color: #28a745;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imagePane = document.getElementById('image-pane');
        const imageTrigger = document.getElementById('image-pane-trigger');

        if (imageTrigger) {
            imageTrigger.addEventListener('click', function() {
                // Show loading overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay-wrapper';
                overlay.innerHTML = `{!! $loadingOverlay !!}`;
                imagePane.appendChild(overlay);

                // Simulate loading delay (remove this in production)
                setTimeout(() => {
                    // Remove loading overlay
                    imagePane.removeChild(overlay);
                    // Show content
                    imagePane.classList.add('loaded');
                }, 500);
            });
        }
    });
</script>
