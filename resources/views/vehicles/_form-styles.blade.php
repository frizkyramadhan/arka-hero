<style>
    /* Select2 bootstrap4 — match RCR / AdminLTE working pattern */
    .vehicle-form-card .select2-container {
        width: 100% !important;
        display: block;
    }

    .vehicle-form-card .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        position: relative;
    }

    .vehicle-form-card .select2-container--bootstrap4 .select2-selection__rendered {
        line-height: 2.25rem !important;
        padding-left: 0.75rem;
        padding-right: 2rem;
    }

    .vehicle-form-card .select2-container--bootstrap4 .select2-selection__arrow {
        position: absolute;
        top: 0;
        right: 3px;
        width: 20px;
        height: 100% !important;
    }

    .vehicle-form-card .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .vehicle-form-card {
        border-radius: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .vehicle-form-card > .card-header {
        border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        background-color: #f8f9fa;
    }

    .vehicle-form-card > .card-body {
        padding: 1.25rem 1.35rem;
    }

    .vehicle-form-card .form-group {
        margin-bottom: 1rem;
    }

    .vehicle-form-card label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #343a40;
    }

    .vehicle-form-card .form-text {
        font-size: 0.8rem;
    }

    .vehicle-form-card .input-group-text {
        background-color: #f4f6f9;
        border-color: #ced4da;
        color: #6c757d;
        min-width: 2.5rem;
        justify-content: center;
    }

    /* Document Validity — compact but cozy sidebar card */
    .vehicle-doc-validity-card > .card-body {
        padding: 0.85rem 0.95rem;
    }

    .vehicle-doc-validity-hint {
        font-size: 0.78rem;
        line-height: 1.35;
        margin-bottom: 0.65rem;
    }

    .vehicle-validity-tile {
        background: #f8f9fb;
        border: 1px solid #e9ecef;
        border-radius: 0.45rem;
        padding: 0.6rem 0.7rem;
        height: auto;
        margin-bottom: 0.55rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .vehicle-validity-tile:hover {
        border-color: #cfd8e3;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .vehicle-validity-tile-head {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.45rem;
    }

    .vehicle-validity-tile-head label {
        font-size: 0.84rem;
        line-height: 1.2;
    }

    .vehicle-validity-tile-head small {
        font-size: 0.72rem;
        line-height: 1.2;
        margin-top: 0.1rem;
    }

    .vehicle-validity-icon {
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 0.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        flex-shrink: 0;
        font-size: 0.78rem;
    }

    .vehicle-validity-tile .form-control-sm {
        height: calc(1.7rem + 2px);
        padding: 0.2rem 0.5rem;
        font-size: 0.85rem;
    }

    .vehicle-form-actions {
        border-radius: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .vehicle-form-actions .card-body {
        padding: 1rem 1.25rem;
    }

    .vehicle-form-actions .btn-block + .btn-block {
        margin-top: 0.5rem;
    }

    #arkfleet-select-wrap.is-loading .select2-container {
        opacity: 0.7;
        pointer-events: none;
    }
</style>
