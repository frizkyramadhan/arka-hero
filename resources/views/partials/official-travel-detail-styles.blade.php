{{-- Shared detail-page styles (aligned with officialtravels/show.blade.php) --}}
<style>
    .content-wrapper-custom {
        background-color: #f8fafc;
        min-height: 100vh;
        padding-bottom: 40px;
    }

    .travel-header {
        position: relative;
        height: 120px;
        color: white;
        padding: 20px 30px;
        margin-bottom: 30px;
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .travel-header-content {
        position: relative;
        z-index: 2;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .travel-number {
        font-size: 13px;
        margin-bottom: 4px;
        opacity: 0.9;
        letter-spacing: 1px;
    }

    .travel-destination {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .travel-date {
        font-size: 14px;
        opacity: 0.9;
    }

    .travel-status-pill {
        position: absolute;
        top: 20px;
        right: 20px;
    }

    .travel-status-pill .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Aligned with approval-requests/show document-status-pill */
    .travel-status-pill .overtime-status-pill {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #ffffff;
    }

    .overtime-pill-draft {
        background-color: #6c757d;
    }

    .overtime-pill-pending {
        background-color: #e67e22;
    }

    .overtime-pill-approved {
        background-color: #28a745;
    }

    .overtime-pill-rejected {
        background-color: #dc3545;
    }

    .overtime-pill-finished {
        background-color: #17a2b8;
    }

    .travel-content {
        padding: 0 20px;
    }

    .travel-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .travel-card .card-head {
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        background-color: #f8f9fa;
    }

    .travel-card .card-head h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .travel-card .card-body {
        padding: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        padding: 20px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-icon {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
        flex-shrink: 0;
    }

    .info-content {
        flex: 1;
        min-width: 0;
    }

    .info-label {
        font-size: 12px;
        color: #777;
        margin-bottom: 4px;
    }

    .info-value {
        font-weight: 600;
        color: #333;
    }

    /* Full-width remarks below the 2×2 info grid (avoids grid-column layout quirks) */
    .overtime-remarks-block {
        padding: 20px;
    }

    .overtime-remarks-item {
        align-items: flex-start;
        gap: 12px;
        width: 100%;
    }

    .overtime-remarks-item .info-icon {
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* Label di atas, value penuh lebar (hindari label + value sejajar salah) */
    .overtime-remarks-item .info-content {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        align-self: stretch;
        flex: 1 1 auto;
        min-width: 0;
        gap: 6px;
    }

    .overtime-remarks-item .info-label {
        margin-bottom: 0;
        width: 100%;
    }

    .overtime-remarks-item .info-value,
    .overtime-remarks-value {
        width: 100%;
        max-width: 100%;
    }

    .overtime-remarks-value {
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.45;
    }

    .travel-action-buttons {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .btn-action {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s;
        gap: 8px;
        color: white;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .back-btn {
        background-color: #64748b;
    }

    .back-btn:hover {
        color: white;
    }

    .edit-btn {
        background-color: #3498db;
    }

    .edit-btn:hover {
        color: white;
    }

    .delete-btn {
        background-color: #e74c3c;
    }

    .delete-btn:hover {
        color: white;
    }

    .finish-btn {
        background: linear-gradient(135deg, #17a2b8, #138496);
    }

    .finish-btn:hover {
        color: white;
    }

    .submit-approval-btn {
        background: linear-gradient(135deg, #28a745, #218838);
    }

    .submit-approval-btn:hover {
        color: white;
    }

    .btn-action:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: white;
    }

    @media (max-width: 992px) {
        .info-grid {
            grid-template-columns: 1fr;
        }

        .travel-content .row {
            display: flex;
            flex-direction: column;
        }

        .travel-content .col-lg-8 {
            order: 1;
            width: 100%;
        }

        .travel-content .col-lg-4 {
            order: 2;
            width: 100%;
        }

        .travel-content {
            padding: 0 15px;
        }
    }

    @media (max-width: 768px) {
        .travel-header {
            height: auto;
            padding: 15px;
            position: relative;
        }

        .travel-header-content {
            padding-right: 80px;
        }

        .travel-destination {
            font-size: 20px;
        }

        .travel-status-pill {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    }

    @media (min-width: 993px) {
        .travel-content .row {
            display: flex;
            flex-wrap: wrap;
        }

        .travel-content .col-lg-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }

        .travel-content .col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
    }
</style>
